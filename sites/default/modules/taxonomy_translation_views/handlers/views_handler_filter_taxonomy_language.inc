<?php

/**
 * Filter by taxonomy language
 */
class views_handler_filter_taxonomy_language extends views_handler_filter_in_operator {
  function get_value_options() {
    if (!isset($this->value_options)) {
      $this->value_title = t('Language');
      $languages = array(
        '***CURRENT_LANGUAGE***' => t("Current user's language"),
        '***DEFAULT_LANGUAGE***' => t("Default site language"),
        LANGUAGE_NONE => t('No language')
      );
      $languages = array_merge($languages, locale_language_list());
      $this->value_options = $languages;
    }
  }
}
