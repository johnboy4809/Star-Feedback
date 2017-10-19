<?php

class Reviews_Admin {
	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->create_reviews_table();
		$this->filters_and_actions();
	}

	public function enqueue_styles() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/reviews-admin.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_media();
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/reviews-admin.js', array( 'jquery', 'wp-color-picker', 'media-upload' ), $this->version, false );
		wp_localize_script( 'jquery', 'postreview', array('ajax_url' => admin_url('admin-ajax.php')));
	}

	public function create_reviews_table() {
		global $wpdb;
		$table = $wpdb->prefix . "wf_reviews";
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS $table (
			`id` mediumint(9) NOT NULL AUTO_INCREMENT,
			`name` text NOT NULL,
			`company` text NOT NULL,
			`position` text NOT NULL,
			`email` text NOT NULL,
			`review` mediumtext NOT NULL,
			`score` mediumint(9) NOT NULL,
			`review_date` datetime NOT NULL,
			`active` int(1) NOT NULL DEFAULT '0',
		UNIQUE (`id`)
		) $charset_collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	public function filters_and_actions() {
		add_action( 'wp_ajax_nopriv_activateReviews', array( $this, 'activate_review') );
		add_action( 'wp_ajax_activateReviews', array( $this, 'activate_review') );
	}

	public function add_plugin_admin_menu() {
    add_options_page(
			'WF Reviews Base Options & Settings',
			'WF Reviews',
			'manage_options',
			$this->plugin_name,
			array($this, 'display_plugin_setup_page')
		);
	}

	public function add_action_links( $links ) {
	   $settings_link = array(
	    '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __('Settings', $this->plugin_name) . '</a>',
	   );
	   return array_merge(  $settings_link, $links );
	}

	public function display_plugin_setup_page() {
    include_once( 'partials/reviews-admin-display.php' );
	}

	public function options_update() {
		register_setting($this->plugin_name.'-widget', $this->plugin_name.'-widget', array($this, 'validate_widget'));
		register_setting($this->plugin_name.'-questions', $this->plugin_name.'-questions', array($this, 'validate_questions'));
		register_setting($this->plugin_name.'-page-settings', $this->plugin_name.'-page-settings', array($this, 'validate_page'));
	}

	public function validate_widget($input) {
		$valid = array();
		$valid['widget'] 				= (isset($input['widget']) && !empty($input['widget'])) ? 1 : 0;
		$valid['link']				 	= $input['link'];
		$valid['widget_background_color'] = (isset($input['widget_background_color']) && !empty($input['widget_background_color'])) ? sanitize_text_field($input['widget_background_color']) : '';
		if ( !empty($valid['widget_background_color']) && !preg_match( '/^#[a-f0-9]{6}$/i', $valid['widget_background_color']  ) ) {
			add_settings_error( 'widget_background_color', 'widget_background_color_texterror', 'Please enter a valid hex value color',  'error' );
		}
		$valid['widget_text_color'] = (isset($input['widget_text_color']) && !empty($input['widget_text_color'])) ? sanitize_text_field($input['widget_text_color']) : '';
		if ( !empty($valid['widget_text_color']) && !preg_match( '/^#[a-f0-9]{6}$/i', $valid['widget_text_color']  ) ) {
			add_settings_error( 'widget_text_color', 'widget_text_color_texterror', 'Please enter a valid hex value color',  'error' );
		}
		return $valid;
	}

	public function validate_questions($input) {
		$valid = array();
		$valid['q']							= $input['q'];
		return $valid;
	}

	public function validate_page($input) {
		$valid = array();
		$valid['review_bg_id'] 	= esc_html($input['review_bg_id']);
		$valid['widget_background_color'] = (isset($input['widget_background_color']) && !empty($input['widget_background_color'])) ? sanitize_text_field($input['widget_background_color']) : '';
		$valid['reviews_bg_id'] = (isset($input['reviews_bg_id']) && !empty($input['reviews_bg_id'])) ? absint($input['reviews_bg_id']) : 0;
   return $valid;
	}

	public function activate_review(){
		global $wpdb;
		$table = $wpdb->prefix."wf_reviews";
		$wpdb->update( $table, array( 'active' => $_POST["checked"]),array('id'=>$_POST['rev_id']));
		echo json_encode($_POST);
		die();
	}

}
