(function( $ ) {
	'use strict';
	$(function(a){

		var frame,
			imgUploadButton 		= $( '#reviews_bg' ),
			imgContainer 				= $( '#upload_logo_preview' ),
			imgIdInput 					= $( '#reviews_bg_id' ),
			imgPreview 					= $('#background_preview'),
			imgDelButton 				= $('#reviews_delete_background_button'),
			addQuestion	 				= $('#addQ'),
			colorPickerInputs 	= $('.reviews-color-picker'),
			dataRow							= $("input[name='active[]']");

		$( '.reviews-color-picker' ).wpColorPicker();

		var b = window.location.hash;
		"" !== b && (a(".nav-tab-wrapper").children().removeClass("nav-tab-active"),
		a('.nav-tab-wrapper a[href="' + b + '"]').addClass("nav-tab-active"),
		a(".tabs-content").children().addClass("hidden"),
		a(".tabs-content div" + b.replace("#", "#tab-")).removeClass("hidden")),
		a(".nav-tab-wrapper a").click(function() {
				var b = a(this).attr("href").replace("#", "#tab-");
				a(this).parent().children().removeClass("nav-tab-active"),
				a(this).addClass("nav-tab-active"),
				a(".tabs-content").children().addClass("hidden"),
				a(".tabs-content div" + b).removeClass("hidden")
		});

		imgUploadButton.on( 'click', function(e) {
			e.preventDefault();
			if ( frame ) {
				frame.open();
				return;
			}
			frame = wp.media({
				title: 'Select or Upload Media for your Login Logo',
				button: {
					text: 'Use as my Login page Logo'
				},
				multiple: false  // Set to true to allow multiple files to be selected
			});
			frame.on( 'select', function() {
				var attachment = frame.state().get('selection').first().toJSON();
				imgPreview.find( 'img' ).attr( 'src', attachment.sizes.thumbnail.url );
				imgIdInput.val( attachment.id );
				imgPreview.removeClass( 'hidden' );
			});
			frame.open();
		});

		imgDelButton.on('click', function(e){
			e.preventDefault();
			imgIdInput.val('');
			imgPreview.find( 'img' ).attr( 'src', '' );
			imgPreview.addClass('hidden');
		});

		addQuestion.on('click', function(e) {
			e.preventDefault();
			$("#q0").clone().insertAfter("tr.questions:last");
		});

		dataRow.on('change', function(e) {
			var ajaxData = new FormData();
			ajaxData.append('action', 'activateReviews');
			ajaxData.append('rev_id', $(this).val());
			if ($(this).is(':checked')) {
				ajaxData.append('checked', 1);
			} else {
				ajaxData.append('checked', 0);
			}
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

	});
})( jQuery );
