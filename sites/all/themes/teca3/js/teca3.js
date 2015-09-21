(function ($) {

/**
 * Attaches the autocomplete behavior to all required fields.
 */
Drupal.behaviors.teca3 = {
  attach: function (context, settings) {
    var height = $('body').outerHeight() - $('#navbar').outerHeight() - $('.footer').outerHeight() - 1;
    $('div.row').css('min-height', height);
  }
};

})(jQuery);
