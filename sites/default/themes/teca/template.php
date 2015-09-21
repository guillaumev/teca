<?php

function teca_preprocess_search_result(&$variables) {
  $node = $variables['result']['node'];
  $variables['classes_array'][] = 'search-result-'.$node->bundle;
  if ($node->bundle == 'technology') {
    $nid = $node->entity_id;
    $node = node_load($nid);
    $ln = $node->language;
    $source_nid = $node->field_source[$ln][0]['nid'];
    $source = node_load($source_nid);
    $variables['info_split']['user'] = l($source->title, 'node/'.$source_nid);
    $variables['info'] = implode(' - ', $variables['info_split']);
  }
}

function teca_form_alter(&$form, &$form_state, $form_id) {
  if ($form_id == 'user_login_block') {
    $form['name']['#title_display'] = 'invisible';
    $form['name']['#default_value'] = t('E-mail');
    $form['name']['#size'] = '17';
    $form['pass']['#title_display'] = 'invisible';
    $form['pass']['#size'] = '17';
    $items = array();
    $items[] = t('Not yet registered ?').' '.l(t('Click here to join'), 'user/register', array('attributes' => array('title' => t('Join TECA'))));
    $items[] = t('Forgot your password ?').' '.l(t('Click here to request a new one'), 'user/password', array('attributes' => array('title' => t('Request a new password'))));
    $form['links']['#markup'] = theme('item_list', array('items' => $items));
    $form['links']['#weight'] = 50;
    $form['actions']['#weight'] = 1;
  }

}

function teca_preprocess_node(&$vars) {
  $vars['top_pager'] = theme('pager');
}

function teca_pdfpreview_formatter($variables) {
  $element = $variables['element'];
  $element['#children'] = str_replace('<a', '<a target="_blank" ', $element['#children']);
  $item = $element['#item'];
  $wrapper_tag = $element['#settings']['tag'];
  $description = ($element['#settings']['show_description'] && isset($item['description'])) ? '<' . $wrapper_tag . ' class="pdfpreview-description">' . $item['description'] . '</' . $wrapper_tag . '>' : '' ;
  return sprintf(
    '<div class="pdfpreview" id="pdfpreview-%s">'
    . ' <%s class="pdfpreview-image-wrapper">%s</%s>'
    . ' %s'
    . '</div>',
    $item['fid'],
    $wrapper_tag, $element['#children'], $wrapper_tag,
    $description
  );
}

