<?php
  /*
  * Template Name: Reviews
  * Description: Get star ratings from your sites users.
  */
	global $wp;
  global $wpdb;
  $table = $wpdb->prefix."wf_reviews";
	$settings					= get_option('reviews-widget', $default = false);
	$bgCol						= $settings['widget_background_color'];
	get_header();
?>

<style>

.details h4, h5, i {
	color: <?php if(!empty($bgCol)) echo $bgCol;?>;
}
</style>

<div class="wrap">
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
      <?php
        $reviews = $wpdb->get_results( "SELECT * FROM {$table} WHERE active = 1");
        foreach ($reviews as $review) {
      ?>
      <div class="entry-content" itemscope itemtype="http://schema.org/Review">
        <meta itemprop="description" content="<?php echo $review->review; ?>">
        <meta itemprop="datePublished" content="<?php echo $review->review_date; ?>">
        <meta itemprop="worstRating" content="1">
        <aside class="details">
          <h4 itemprop="name" itemprop="author" itemscope itemtype="http://schema.org/Person"><?php echo $review->name; ?> <small>(<?php echo $review->position; ?>)</small></h4>
          <h5><?php echo $review->company; ?></h5>
          <?php for ($i = 1; $i <= $review->score; $i++) { ?>
            <i class='fa fa-star' aria-hidden='true'></i>
          <?php } ?>

					<div class="review">
						<p itemprop="reviewBody"><?php echo $review->review; ?></p>
					</div>
        </aside>

      </div>
      <?php } ?>
    </main><!-- #main -->
	</div><!-- #primary -->
</div><!-- .wrap -->

<?php get_footer(); ?>
