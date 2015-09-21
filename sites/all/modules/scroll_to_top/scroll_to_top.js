(function($){
/**
 * Toggle the visibility of the scroll to top link.
 */
 
Drupal.behaviors.scroll_to_top = {
  attach: function (context, settings) {
        var bottom_enabled = settings.scroll_to_top.enable_scroll_bottom;
	// append  back to top link top body if it is not
	var exist= jQuery('#back-top').length; // exist = 0 if element doesn't exist
	if(exist == 0){ // this test is for fixing the ajax bug 
		$("body").append("<p id='back-top'><a href='#top'><span id='button'></span><span id='link'>" + settings.scroll_to_top.label + "</span></a></p>");
	}
        if (bottom_enabled) {
          // append scroll to bottom link
          $("body").append("<p id='scroll-bottom'><a href='#scroll_bottom'><span id='bottom-button'></span><span id='bottom-link'>" + settings.scroll_to_top.bottom_label + "</span></a></p>");
        }
	// Preview function
	$("input").change(function () {
		// building the style for preview
		var style="<style>#scroll-to-top-prev-container #back-top-prev span#button-prev{ background-color:"+$("#edit-scroll-to-top-bg-color-out").val()+";} #scroll-to-top-prev-container #back-top-prev span#button-prev:hover{ background-color:"+$("#edit-scroll-to-top-bg-color-hover").val()+" }</style>"
		// building the html content of preview
		var html="<p id='back-top-prev' style='position:relative;'><a href='#top'><span id='button-prev'></span><span id='link'>";
		// if label enabled display it
		if($("#edit-scroll-to-top-display-text").attr('checked')){
		html+=$("#edit-scroll-to-top-label").val();
		}
		html+="</span></a></p>";
		// update the preview
		$("#scroll-to-top-prev-container").html(style+html);
	});
	$("#back-top").hide();
	$(function () {
          if (bottom_enabled) {
            $(window).scroll(function () {
              if ($(this).scrollTop() > 100) {
                 $('#back-top').fadeIn();
                 $('#scroll-bottom').fadeOut();
              } else {
              $('#back-top').fadeOut();
              $('#scroll-bottom').fadeIn();
            }
          });
        }
        else {
          $(window).scroll(function () {
           if ($(this).scrollTop() > 100) {
             $('#back-top').fadeIn();
           } else {
             $('#back-top').fadeOut();
           }
         });
        }
		// scroll body to 0px on click
		$('#back-top a').click(function () {
			$('body,html').animate({
				scrollTop: 0
			}, 800);
			return false;
		});
	});
        if (bottom_enabled) {
          $('#scroll-bottom a').click(function() {
          $('body,html').animate({
            scrollTop: $(document).height()
            }, 800);
          return false;
        });
        }
	}
};
})(jQuery);
