<?php

/**
 * Implements hook_variable_group_info()
 */
function tecamod_variable_group_info() {
  // Group for variable that have no group
  $groups['tecamod'] = array(
    'title' => t('TECA'),
    'description' => t("Emails used in TECA."),
  );
  return $groups;
}

/**
 * @file Declares variables needed for TECA
 */
function tecamod_variable_info($options) {
  $variables['tecamod_daily_notification_[mail_part]'] = array(
    'title' => t('Daily Notification'),
    'type' => 'mail_html',
    'description' => t('Template for the daily notification.'),
    'children' => array(
      'tecamod_daily_notification_subject' => array(
        'default' => '[TECA] Daily Notification',
      ),
      'tecamod_daily_notification_body' => array(
        'default' => ''
      ),
    ),
    'group' => 'tecamod',
  );
  
  $variables['tecamod_weekly_notification_[mail_part]'] = array(
    'title' => t('Weekly Notification'),
    'type' => 'mail_html',
    'description' => t('Template for the weekly notification.'),
    'children' => array(
      'tecamod_weekly_notification_subject' => array(
        'default' => '[TECA] Weekly Notification',
      ),
      'tecamod_weekly_notification_body' => array(
        'default' => ''
      ),
    ),
    'group' => 'tecamod',
  );
  
  return $variables;
}
