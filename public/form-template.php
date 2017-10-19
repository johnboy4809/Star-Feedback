<?php
  /*
  * Template Name: Review Form
  * Description: Get star ratings from your sites users.
  */
	global $wp;
	$settings					= get_option('reviews-widget', $default = false);
	$reviews 					= get_option('reviews-questions', $default = false);
	$background 			= get_option('reviews-page-settings', $default = false);
	$bgCol						= $settings['widget_background_color'];
	$review_bg        = wp_get_attachment_image_src( $background['review_bg_id'], 'full' );
	$review_bg_url    = $review_bg[0];
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg reviews-form">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">

	<?php wp_head(); ?>

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.0.0-beta.3/assets/owl.carousel.min.css" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.0.0-beta.3/assets/owl.theme.default.min.css" />

	<style>
	.reviews-form {
	  background: url(<?php if(!empty($review_bg_url)) echo $review_bg_url;?>) no-repeat center center fixed;;
		-webkit-background-size: cover;
		-moz-background-size: cover;
		-o-background-size: cover;
		background-size: cover;
	}
	body {
		background:none;
	}
	.star {
		color: <?php if(!empty($bgCol)) echo $bgCol;?>;
	}
	.owl-theme .owl-dots .owl-dot span {
		opacity: 0.6;
		background: <?php if(!empty($bgCol)) echo $bgCol;?>;
	}
	.owl-theme .owl-dots .owl-dot span:hover {
		opacity: 1.0;
		background: <?php if(!empty($bgCol)) echo $bgCol;?>;
	}
	.owl-theme .owl-dots .owl-dot.active span {
		opacity: 1.0;
		background: <?php if(!empty($bgCol)) echo $bgCol;?>;
	}
	.owl-theme .owl-nav [class*=owl-] {
		color: <?php if(!empty($bgCol)) echo $bgCol;?>;
		font-size: 2em;
		background: none;
	}
	.owl-theme .owl-nav [class*=owl-]:hover {
		background: none;
	}
	.lastSlide {
		border: 1px solid <?php if(!empty($bgCol)) echo $bgCol;?>;
	}
	.lastSlide input, textarea {
		border: 1px solid <?php if(!empty($bgCol)) echo $bgCol;?>;
	}
  .lastSlide .submit {
		background: <?php if(!empty($bgCol)) echo $bgCol;?>;
	}
	.reviewGuideWrap .reviewGuide span:before {
		color: <?php if(!empty($bgCol)) echo $bgCol;?>;
	}
	</style>
</head>

<body <?php body_class(); ?>>
	<?php while (have_posts()) : the_post(); ?>
	<header class="entry-header">
		<!-- <?php the_title( '<h2 class="entry-title">', '</h2>' ); ?> -->
	</header>
	<div class= "reviewWrap" >
		<form action="" method="post" class="wf-review-form" data-id="<?php echo get_the_ID(); ?>">
			<input type="hidden" value="<?php echo count($reviews['q']); ?>" name="count">
			<div class="owl-carousel owl-theme" id="reviewQuestions">
				<?php $q = 1; foreach ($reviews['q'] as $question) { if (empty($question)) { continue; }; ?>
					<fieldset class="rating item">
						<h2><?php echo $question; ?></h2>
						<div class="stars">
						<?php for ($i = 5; $i > 0; $i--) { ?>
							<input type="radio" id="star<?php echo $q; ?>-<?php echo $i; ?>" name="rating<?php echo $q; ?>" value="<?php echo $i; ?>" />
							<label class="star" for="star<?php echo $q; ?>-<?php echo $i; ?>" title="<?php echo $i; ?> Stars"></label>
						<?php } ?>
						</div>
					</fieldset>
				<?php $q++; } ?>
				<fieldset class="item lastSlide textfield">
					<h2>Thank you for taking the time to answer our questions</h2>
					<p>Please fill out your contact details and your review will be submitted</p>
          <input type="text" name="name" placeholder="Name">
          <input type="text" name="company" placeholder="Company">
          <input type="text" name="position" placeholder="Position">
          <input type="email" name="email" placeholder="Email">
          <textarea name="review" placeholder="Your overall opinion of our service"></textarea>
          <input class="submit" type="submit" value="Submit">
        </fieldset>
			</div>
		</form>
	</div> <?php // ReviewWrap ?>
	<div class= "reviewGuideWrap">
		<div class= "reviewGuide">
			<span>Very Poor</span>
			<span>Excellent</span>
		</div>
	</div>
	<?php endwhile; ?>
<?php wp_footer(); ?>

</body>
</html>
