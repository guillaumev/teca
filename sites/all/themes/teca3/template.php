<?php

/**
 * @file template.php
 */
 
/**
 * Implements hook_preprocess_region().
 */
function teca3_preprocess_region(&$variables) {
  global $theme;
  static $wells;
  if (!isset($wells)) {
    foreach (system_region_list($theme) as $name => $title) {
      $wells[$name] = theme_get_setting('bootstrap_region_well-' . $name);
    }
  }

  switch ($variables['region']) {
    // @todo is this actually used properly?
    case 'content':
      $variables['theme_hook_suggestions'][] = 'region__no_wrapper';
      break;

    case 'help':
      $variables['content'] = _bootstrap_icon('question-sign') . $variables['content'];
      $variables['classes_array'][] = 'alert';
      $variables['classes_array'][] = 'alert-info';
      break;
    case 'footer':
      $variables['classes_array'][] = 'container';
      break;
  }
  if (!empty($wells[$variables['region']])) {
    $variables['classes_array'][] = $wells[$variables['region']];
  }
}

/**
 * Bootstrap theme wrapper function for the footer menu links.
 */
function teca3_menu_tree__menu_footer_menu(&$variables) {
  return '<ul class="menu nav nav-pills nav-justified">' . $variables['tree'] . '</ul>';
}

function teca3_links__locale_block(&$variables) {
  $variables['attributes']['class'][] = 'nav';
  $variables['attributes']['class'][] = 'navbar-nav';
  return theme_links($variables);
  //return '<ul class="language-switcher-locale-url nav nav-pills">'.$variables['tree'].'</ul>';
}

/**
 * Implements hook_preprocess_page().
 *
 * @see page.tpl.php
 */
function teca3_preprocess_page(&$variables) {
  // Add information about the number of sidebars.
  if (!empty($variables['page']['sidebar_first']) && !empty($variables['page']['sidebar_second'])) {
    $variables['content_column_class'] = ' class="col-sm-6"';
  }
  elseif (!empty($variables['page']['sidebar_first']) || !empty($variables['page']['sidebar_second'])) {
    $variables['content_column_class'] = ' class="col-sm-9 section-content"';
  }
  else {
    $variables['content_column_class'] = ' class="col-sm-12"';
  }

  // Primary nav.
  $variables['primary_nav'] = FALSE;
  if ($variables['main_menu']) {
    // Build links.
    $variables['primary_nav'] = menu_tree(variable_get('menu_main_links_source', 'main-menu'));
    // Provide default theme wrapper function.
    $variables['primary_nav']['#theme_wrappers'] = array('menu_tree__primary');
  }

  // Secondary nav.
  $variables['secondary_nav'] = FALSE;
  if ($variables['secondary_menu']) {
    // Build links.
    $variables['secondary_nav'] = menu_tree(variable_get('menu_secondary_links_source', 'user-menu'));
    // Provide default theme wrapper function.
    $variables['secondary_nav']['#theme_wrappers'] = array('menu_tree__secondary');
  }

  $variables['navbar_classes_array'] = array('navbar');

  if (theme_get_setting('bootstrap_navbar_position') !== '') {
    $variables['navbar_classes_array'][] = 'navbar-' . theme_get_setting('bootstrap_navbar_position');
  }
  else {
    $variables['navbar_classes_array'][] = 'container';
  }
  if (theme_get_setting('bootstrap_navbar_inverse')) {
    $variables['navbar_classes_array'][] = 'navbar-inverse';
  }
  else {
    $variables['navbar_classes_array'][] = 'navbar-default';
  }
}


function teca3_preprocess_views_exposed_form(&$vars, $hook) {
    switch($vars['form']['#id']) {            
        // Views search page
        case "views-exposed-form-search-master": 
          // Change the text on the submit button
          $vars['form']['submit']['#value'] = '<span class="glyphicon glyphicon-search"></span>';
          // Rebuild the form
          unset($vars['form']['submit']['#printed']);
          $vars['button'] = drupal_render($vars['form']['submit']);      
          break;
    }
}

/**
 * Implements hook_preprocess_block().
 */
/*function bootstrap_preprocess_block(&$variables) {
  // Use a bare template for the page's main content.
  if ($variables['block_html_id'] == 'block-locale-language') {
    $variables['classes'][] = 'container';
  }
}*/
