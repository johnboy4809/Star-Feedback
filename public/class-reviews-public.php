<?php
class Reviews_Public {
	private $plugin_name;
	private $version;
	protected $templates;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->reviews_options = get_option($this->plugin_name.'-widget');
		$this->filters_and_actions();
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/reviews-public.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'owl', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.0.0-beta.3/owl.carousel.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/reviews-public.js', array( 'jquery', 'owl' ), $this->version, false );
		wp_enqueue_script( 'fontawesome', 'https://use.fontawesome.com/2676bd5218.js', array( 'jquery' ) );
		wp_localize_script( 'jquery', 'postreview', array('ajax_url' => admin_url('admin-ajax.php')));
	}

	public function filters_and_actions() {
		$this->templates = array();
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {
			add_filter('page_attributes_dropdown_pages_args', array( $this, 'register_project_templates'));
		} else {
			add_filter('theme_page_templates', array( $this, 'add_new_template'));
		}
		add_filter('wp_insert_post_data', array( $this, 'register_project_templates'));
		add_filter('template_include', array( $this, 'view_project_template'));
		$this->templates = array('form-template.php' => 'Review Form', 'reviews.php' => 'Reviews Page');
		if ($this->reviews_options['widget']) {
			add_action('wp_footer', array( $this, 'reviews_widget'));
		}
		add_action( 'wp_ajax_nopriv_save_review', array( $this, 'save_review') );
		add_action( 'wp_ajax_save_review', array( $this, 'save_review') );
	}

	public function add_new_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, $this->templates );
		return $posts_templates;
	}

	public function register_project_templates( $atts ) {
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		}
		wp_cache_delete( $cache_key , 'themes');
		$templates = array_merge( $templates, $this->templates );
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );
		return $atts;
	}

	public function view_project_template( $template ) {
		global $post;
		if (!$post) {
			return $template;
		}
		if (!isset($this->templates[get_post_meta($post->ID, '_wp_page_template', true )])) {
			return $template;
		}
		$file = plugin_dir_path(__FILE__). get_post_meta($post->ID, '_wp_page_template', true);
		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}
		return $template;
	}

	public function reviews_widget() {
		global $post;
		global $wpdb;
		$table 				= $wpdb->prefix."wf_reviews";
		$data 				= $wpdb->get_results( "SELECT * FROM {$table} WHERE active = 1");
   	$reviews 			= count($data);
		$score   			= round(array_sum(array_column($data,'score')) / $reviews, 1, PHP_ROUND_HALF_UP);
   	$product 			= get_the_title($post->ID);
		$background		= ($this->reviews_options['widget_background_color']) ? $this->reviews_options['widget_background_color'] : '#000000';
		$text					= ($this->reviews_options['widget_text_color']) ? $this->reviews_options['widget_text_color'] : '#FFFFFF';
		$link 				= ($this->reviews_options['link']) ? $this->reviews_options['link'] : null;
		if (is_page()) {
		   $ratingoutput = '<script type="application/ld+json">{"@context": "http://schema.org","@type": "Product","name": "' . $product . '","aggregateRating": {"@type": "AggregateRating","ratingValue": "' . $score . '","reviewCount": "'. $reviews . '"}}</script>';
			 $widget = "<div id='starRating' style='background-color:{$background}'><div class='starWrap'><span style='color:{$text};'>{$score}</span>";
			 for ($i = 1; $i <= $score; $i++) {
				 $widget .= "<i class='fa fa-star' aria-hidden='true' style='color:{$text};'></i>";
			 }
			 $widget .= "</div><a href='".get_page_link($link)."' style='color:{$text};'>From {$reviews} Customer Reviews</a></div>";
			 echo $ratingoutput.$widget;
		}
	}

	public function save_review() {
	  global $wpdb;
	  $table = $wpdb->prefix."wf_reviews";
		$score = 0;
		for ($i=1; $i <= $_POST['count'] ; $i++) {
			$score = ($score + $_POST["rating$i"]);
		}
		$_POST['score'] 			= round($score / $_POST['count']);
		$wpdb->insert(
			$table ,
			array(
				'name' 					=> sanitize_text_field($_POST["name"]),
				'company' 			=> sanitize_text_field($_POST["company"]),
				'position' 			=> sanitize_text_field($_POST["position"]),
				'email' 				=> sanitize_text_field($_POST["email"]),
				'review' 				=> sanitize_text_field($_POST["review"]),
				'score' 				=> $_POST['score'],
				'review_date' 	=> date('Y-m-d H:i:s'),
			)
		);
	  echo json_encode($_POST);
	}

}
