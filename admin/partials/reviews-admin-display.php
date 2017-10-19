<?php
  if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
  }
  class Reviews_Table extends WP_List_Table { // See tutorial https://www.sitepoint.com/using-wp_list_table-to-create-wordpress-admin-tables/

    public function __construct() {
      parent::__construct( [
        'singular' => 'Review',
        'plural'   => 'Reviews',
        'ajax'     => false
      ] );
    }

    public static function get_data($per_page=10, $page_number=1) {
      global $wpdb;
      $sql = "SELECT * FROM {$wpdb->prefix}wf_reviews";
      if ( ! empty( $_REQUEST['orderby'] ) ) {
        $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
        $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
      }
      $sql .= " LIMIT {$per_page}";
      $sql .= ' OFFSET ' .($page_number - 1) * $per_page;
      $reviews = $wpdb->get_results( $sql, ARRAY_A );
      foreach ($reviews as $review) {
        $review['score'] = self::get_stars($review['score']);
        $data[] = $review;
      }
      return $data;
    }

    public static function delete_review( $id ) {
      global $wpdb;
      $wpdb->delete(
        "{$wpdb->prefix}customers",
        [ 'id' => $id ],
        [ '%d' ]
      );
    }

    public static function record_count() {
      global $wpdb;
      $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}wf_reviews";
      return $wpdb->get_var( $sql );
    }

    public function no_items() {
      echo 'No reviews avaliable.';
    }

    function column_name( $item ) {
      $delete_nonce = wp_create_nonce( 'delete_review' );
      $title = '<strong>' . $item['name'] . '</strong>';
      $actions = [
        'delete' => sprintf( '<a href="?page=%s&action=%s&review=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce )
      ];
      return $title . $this->row_actions( $actions );
    }

    public function column_default( $item, $column_name ) {
      switch( $column_name ) {
        case 'name';
        case 'company';
        case 'position';
        case 'email';
        case 'score';
        case 'review';
        case 'review_date';
        case 'active';
          return $item[ $column_name ];
        default:
          return print_r( $item, true );
        }
      }

      function column_cb( $item ) {
        return sprintf('<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']);
      }

      function column_active( $item ) {
        $checked = ($item['active']) ? 'checked' : null;
        return sprintf('<input type="checkbox" name="active[]" data-id="'.$item['id'].'" value="'.$item['id'].'" '.$checked.' />', $item['active']);
      }

      function get_columns(){
        $columns = array(
          'cb'          => '<input type="checkbox" />',
          'name'        => 'Name',
          'company'     => 'Company',
          'position'    => 'Position',
          'email'       => 'Email',
          'score'       => 'Score',
          'review'      => 'Review',
          'review_date' => 'Date',
          'active'      => 'Active'
        );
        return $columns;
      }

      public function get_sortable_columns() {
        $sortable_columns = array(
          'name'        => array( 'name', false ),
          'score'       => array( 'score', false ),
          'review_date' => array( 'review_date', true )
        );
        return $sortable_columns;
      }

      public function get_bulk_actions() {
        $actions = [
          'bulk-delete' => 'Delete'
        ];
        return $actions;
      }

      public function prepare_items() {
        $this->process_bulk_action();
        $per_page     = $this->get_items_per_page( 'reviews_per_page', 30  );
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();
        $this->set_pagination_args( [
          'total_items' => $total_items,
          'per_page'    => $per_page
        ] );
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = self::get_data( $per_page, $current_page );
      }

      public function process_bulk_action() {
        if ( 'delete' === $this->current_action() ) {
          $nonce = esc_attr( $_REQUEST['_wpnonce'] );
          if ( ! wp_verify_nonce( $nonce, 'delete_review' ) ) {
            die( 'Go get a life script kiddies' );
          } else {
            self::delete_review( absint( $_GET['customer'] ) );
            wp_redirect( esc_url( add_query_arg() ) );
            exit;
          }
        }
        if ((isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' ) || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )) {
          $delete_ids = esc_sql( $_POST['bulk-delete'] );
          foreach ( $delete_ids as $id ) {
            self::delete_review( $id );
          }
          wp_redirect( esc_url( add_query_arg() ) );
          exit;
        }
      }

      static function get_stars($count) {
        $stars = "";
        for ($i = 1; $i <= $count; $i++) {
          $stars .= "<span class='dashicons dashicons-star-filled'></span>";
        }
        return $stars;
      }

      public function ajax_response() {

      }
  }

?>

<div class="wrap">

  <h2><?php echo esc_html(get_admin_page_title()); ?></h2>

  <div id="content" class="<?php echo "{$this->plugin_name}-settings-wrapper"; ?>">

    <div class="nav-tab-wrapper">
      <a class="nav-tab nav-tab-active" href="#reviews">Reviews</a>
      <a class="nav-tab" href="#widget">Widget Settings</a>
      <a class="nav-tab" href="#questions">Feedback Questions</a>
      <a class="nav-tab" href="#review-page">Review Page Settings</a>
    </div>

    <div class="tabs-content">
      <div id="tab-reviews" class="">
        <?php
          $ReviewsTable = new Reviews_Table();
          $ReviewsTable->prepare_items();
          $ReviewsTable->display();
        ?>
      </div>
      <div id="tab-widget" class="hidden">
        <form method="post" name="widget-settings" action="options.php">
          <?php
            $form             = $this->plugin_name.'-widget';
            $options          = get_option($form);
            $widget           = $options['widget'];
            $backgroundCol    = $options['widget_background_color'];
            $textCol          = $options['widget_text_color'];
            $link             = $options['link'];
            settings_fields($form);
            do_settings_sections($form);
          ?>
          <table class="form-table">
            <tbody>
              <tr valign="top">
                <th scope="row"><label for="<?php echo $form; ?>-widget">Display Widget</label></th>
                <td>
                  <label><input type="radio" value="1" name="<?php echo $form; ?>[widget]" <?php checked($widget, 1); ?>> Show</label> <br />
                  <label><input type="radio" value="0" name="<?php echo $form; ?>[widget]" <?php checked($widget, 0); ?>> Hide</label>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><label for="<?php echo $form;?>-widget_background_color"> Widget Background Colour</label></th>
                <td>
                  <input type="text" class="<?php echo $this->plugin_name;?>-color-picker" id="<?php echo $form;?>-widget_background_color" name="<?php echo $form;?>[widget_background_color]" value="<?php if(!empty($backgroundCol)) echo $backgroundCol;?>" /><br>
                  <p class="description"></p>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><label for="<?php echo $form;?>-widget_text_color"> Widget Text Colour</label></th>
                <td>
                  <input type="text" class="<?php echo $this->plugin_name;?>-color-picker" id="<?php echo $form;?>-widget_text_color" name="<?php echo $form;?>[widget_text_color]" value="<?php if(!empty($textCol)) echo $textCol;?>" /><br>
                  <p class="description"></p>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><label for="<?php echo $form; ?>-link"> Feedback Page Link.</label></th>
                <td>
                  <?php
                    $args = array('depth' => 0,'name' => $form.'[link]','id' => $form.'-link', 'selected' => $link,);
                    wp_dropdown_pages( $args );
                  ?>
                  <p class="description"></p>
                </td>
              </tr>
            </tbody>
          </table>
          <?php submit_button('Save Widget Settings', 'primary','submit', TRUE); ?>
        </form>
      </div>
      <div id="tab-questions" class="hidden">
        <form method="post" name="feedback-questions" action="options.php">
          <?php
            $form2            = $this->plugin_name.'-questions';
            $options2         = get_option($form2);
            $q                = $options2['q'];
            $count            = ($q) ? count($q) : 1;
            settings_fields($form2);
            do_settings_sections($form2);
          ?>
           <table class="form-table">
             <tbody>
               <?php for ($i = 0; $i < $count; $i++) { ?>
                 <tr valign="top" class="questions" id="<?php echo 'q'.$i; ?>">
                   <th><label for="<?php echo $form2; ?>-q">Question</label></th>
                   <td>
                     <input type="text" class="large-text" id="<?php echo $form2; ?>-q" name="<?php echo $form2; ?>[q][]" value="<?php if(!empty($q[$i])) echo $q[$i]; ?>"/><br>
                   </td>
                 </tr>
               <?php } ?>
               <tr valign="top">
                 <th scope="row"></th>
                 <td><button id="addQ">Add Question</button></td>
               </tr>
             </tbody>
           </table>
          <?php submit_button('Save Question', 'primary','submit', TRUE); ?>
        </form>
      </div>
      <div id="tab-review-page" class="hidden">
        <form method="post" name="review-page" action="options.php">
          <?php
            $form3            = $this->plugin_name.'-page-settings';
            $options3         = get_option($form3);
            $review_bg_id     = $options3['review_bg_id'];
            $review_bg        = wp_get_attachment_image_src( $review_bg_id, 'thumbnail' );
            $review_bg_url    = $review_bg[0];
            settings_fields($form3);
            do_settings_sections($form3);
          ?>
          <input type="hidden" id="reviews_bg_id" name="<?php echo $form3;?>[review_bg_id]" value="<?php if(!empty($review_bg_id)) echo $review_bg_id;?>" />
          <table class="form-table">
            <tbody>
              <tr valign="top">
                <th><label for="<?php echo $form3;?>-review_bg">Select Page Background</label></th>
                <td>
                  <input id="<?php echo $this->plugin_name;?>_bg" type="button" class="button" value="Upload Image" />
                </td>
              </tr>
              <tr valign="top">
                <ht></ht>
                <td>
                  <div id="background_preview" class="<?php echo $this->plugin_name;?>-upload-preview <?php if(empty($review_bg_id)) echo 'hidden'?>">
                    <img src="<?php echo $review_bg_url; ?>" />
                    <button id="<?php echo $this->plugin_name;?>_delete_background_button" class="<?php echo $this->plugin_name;?>-delete-image">Delete X</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          <?php submit_button('Save Page Settings', 'primary','submit', TRUE); ?>
        </form>
      </div>
    </div> <?php // Close tabs-content ?>

  </div>  <?php // Close settings-wrapper ?>

</div> <?php // Close wrap ?>
