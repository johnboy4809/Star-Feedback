(function( $ ) {
	'use strict';
	$(function(){
		$('body').on('submit', '.wf-review-form', function(e) {
			e.preventDefault();
			var post_id     = jQuery(this).data('id');
			var ajaxData    = new FormData(jQuery(this).get(0));
			ajaxData.append('post_id', post_id);
	    ajaxData.append('action', 'save_review');
			jQuery.ajax({
				url : postreview.ajax_url,
				type : 'post',
				data : ajaxData,
				contentType: false,
				processData: false,
				success : function( response ) {
					console.log(response);
				},
				error: function(xhr, status, error) {
					console.log(xhr.responseText);
				}
			});
			return false;
		});

		$("#reviewQuestions").owlCarousel({
			items : 1,
	    slideSpeed : 20,
	    nav: true,
	    autoplay: false,
	    dots: true,
	    responsiveRefreshRate : 200,
	    navText: [''],
		})
		.on('changed.owl.carousel')
		.on('click', '.star', function(){
			$(this).trigger('next.owl.carousel');
		});

	});

})( jQuery );
