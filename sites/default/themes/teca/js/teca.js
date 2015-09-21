(function ($) {
function clear_on_focus(element) {
  var element_value = element.val();
  element.focus(function() {
    if (element.val() == element_value) {
      element.val('');
    }
  });
}

$(document).ready(function() {
  if ($('#edit-name')) {
    var element = $('#edit-name');
    clear_on_focus(element);
  }

  if ($('#edit-pass')) {
    var element = $('#edit-pass');
    clear_on_focus(element);
  }
  
  if ($('input[name=search_block_form]')) {
    var element = $('input[name=search_block_form]');
    element.val(Drupal.t('Search the whole site'));
    clear_on_focus(element);
  }
  
  if ($('#block-views-exp-search-master input[type=text]')) {
    var element = $('#block-views-exp-search-master input[type=text]');
    element.val(Drupal.t('Search the whole site'));
    clear_on_focus(element);
  }
});
})(jQuery);
