<?php

/**
* Implements hook_variable_info().
*/
function terms_of_use_variable_info($options) {

  $variable['terms_of_use_fieldset_name'] = array(
    'title' => t('Terms of Use Fieldset name'),
    'description' => t('Name of the fieldset used in the Terms of Use module'),
    'type' => 'string'
  );

  $variable['terms_of_use_checkbox_label'] = array(
    'title' => t('Terms of Use checkbox label'),
    'description' => t('Label of the checkbox used in the Terms of Use module'),
    'type' => 'string'
  );
  return $variable;
}
