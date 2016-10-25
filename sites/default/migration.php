<?php

error_reporting(E_ALL);

echo "beginning";
/**
 * Migrate TECA users
 */
function teca_migrate_users() {
  // Load users
  $query = new EntityFieldQuery();

  $query->entityCondition('entity_type', 'user', '=');

  $result = $query->execute();

  $new_users = [];
  foreach ($result['user'] as $record) {
    $user = entity_load_single('user', $record->uid);
    $wrapper = entity_metadata_wrapper('user', $user);
    $gender = $wrapper->field_sex->value();
    $year_birth = $wrapper->field_year_birth->value();
    $year_birth = substr($year_birth, 0, 4);
    $new_users[] = array(
      'uid' => $user->uid,
      'name' => $user->name,
      'pass' => $user->pass,
      'mail' => $user->mail,
      'created' => $user->created,
      'access' => $user->access,
      'login' => $user->login,
      'status' => $user->status,
      'timezone' => $user->timezone,
      'title' => $wrapper->field_title->value(),
      'first_name' => $wrapper->field_first_name->value(),
      'last_name' => $wrapper->field_last_name->value(),
      'gender' => $gender[0],
      'year_birth' => empty($year_birth) ? 'NULL' : $year_birth,
      'occupation' => $wrapper->field_occupation->value()->name,
      'sector' => $wrapper->field_sector->value()->name,
      'beekeeper' => empty($wrapper->field_beekeeper->value()) ? 0 : 1,
      'beekeeping_practice' => $wrapper->field_beekeeping_type->value()->name,
      'number_hives' => empty($wrapper->field_number_hives->value()) ? 'NULL' : $wrapper->field_number_hives->value(),
      'notifications' => empty($wrapper->field_user_notifications->value()) ? 'NULL' : $wrapper->field_user_notifications->value(),
      'picture' => empty($user->picture) ? 0 : $user->picture->fid,
      'country_id' => $wrapper->field_country->value()->iso2,
      //'organization' => $wrapper->field_organization->value(),
      'language_id' => substr($user->language, 0, 2),
    );
  }

  return $new_users;
}

/**
 * Migrate TECA partners
 */
function teca_migrate_partners() {
  // Load users
  $query = new EntityFieldQuery();

  $query->entityCondition('entity_type', 'node', '=')
    ->entityCondition('bundle', 'partner');

  $result = $query->execute();

  $new_partners = [];
  foreach ($result['node'] as $record) {
    $node = entity_load_single('node', $record->nid);
    $wrapper = entity_metadata_wrapper('node', $node);
    $contact_name = '';
    $contact_email = '';
    if (!empty($wrapper->field_contacts->value())) {
      $fc_wrapper = entity_metadata_wrapper('field_collection_item', $wrapper->field_contacts->value()[0]);
      $contact_name = $fc_wrapper->field_contact_person->value();
      $contact_email = $fc_wrapper->field_contact_email->value();
    }
    $new_partners[] = array(
      'id' => $node->nid,
      'title' => $node->title,
      'uid' => $node->uid,
      'status' => $node->status,
      'created' => date('Y-m-d H:i:s', $node->created),
      'changed' => date('Y-m-d H:i:s', $node->changed),
      'description' => $wrapper->body->value()['value'],
      'contact_name' => $contact_name,
      'contact_email' => $contact_email,
      'telephone' => $wrapper->field_telephone->value(),
      'web' => $wrapper->field_url->value()['url'],
      'language_id' => $node->language,
      'country_id' => $wrapper->field_country->value()->iso2,
    );
  }

  return $new_partners;
}

/**
 * Migrate TECA technologies
 *
 */
function teca_migrate_technologies($all_keywords_sql) {
  $all_keywords = array();
  foreach ($all_keywords_sql as $all_keyword_sql) {
    $all_keywords[$all_keyword_sql['agrovoc_id']] = $all_keyword_sql['id'];
  }
  $all_keywords_agrovoc = array();
  // Load technologies
  $query = new EntityFieldQuery();

  $query->entityCondition('entity_type', 'node', '=')
    ->entityCondition('bundle', 'technology');

  $result = $query->execute();

  $new_techs = [];
  $tnids = [];
  foreach ($result['node'] as $record) {
    $node = entity_load_single('node', $record->nid);
    if ($node->tnid == 0 || !in_array($node->tnid, $tnids)) {
      $tnids[] = $node->tnid;
      $wrapper = entity_metadata_wrapper('node', $node);
      $images = [];
      foreach ($wrapper->field_images->value() as $image) {
        $images[] = $image;
      }
      $sources = [];
      foreach ($wrapper->field_source->value() as $source) {
        $sources[] = $source->nid;
      }
      $categories = [];
      foreach ($wrapper->field_category->value() as $cat) {
        $categories[] = $cat->tid;
      }
      $regions = [];
      foreach ($wrapper->field_technology_region->value() as $region) {
        $regions[] = $region->tid;
      }
      $countries = [];
      if (isset($node->field_countries[$node->language])) {
        foreach ($node->field_countries[$node->language] as $iso2) {
          $countries[] = $iso2['iso2'];
        }
      }
      $keywords = [];
      foreach ($wrapper->field_keywords->value() as $keyword) {
        if (!isset($all_keywords_agrovoc[$keyword->tid])) {
          $sql = "SELECT t.agrovoc_id FROM {taxonomy_term_data} t WHERE t.tid = :tid;";
          $result = db_query($sql, array(':tid' => $keyword->tid))->fetchField();
          $all_keywords_agrovoc[$keyword->tid] = $result;
        }
        $keywords[] = $keyword->tid;
      }
      $afiles = [];
      foreach ($wrapper->field_attached_files->value() as $afile) {
        $afiles[] = $afile['fid'];
      }
      $see_also = [];
      foreach ($wrapper->field_see_also->value() as $other) {
        $see_also[] = $other->nid;
      }
      if ($node->tnid) {
        $translations = translation_node_get_translations($node->tnid);
        if (!empty($translations)) {
          $new_techs['technologies'][] = array(
            'id' => $node->nid,
            'title_en' => isset($translations['en']) ? $translations['en']->title : '',
            'title_fr' => isset($translations['fr']) ? $translations['fr']->title : '',
            'title_es' => isset($translations['es']) ? $translations['es']->title : '',
            'title_ar' => '',
            'title_cn' => '',
            'available_en' => isset($translations['en']) ? 1 : 0,
            'available_fr' => isset($translations['fr']) ? 1 : 0,
            'available_es' => isset($translations['es']) ? 1 : 0,
            'available_ar' => 0,
            'available_cn' => 0,
            'status' => $node->status,
            'incomplete' => !empty($node->field_technology_incomplete[$node->language][0]['value']) ? $node->field_technology_incomplete[$node->language][0]['value'] : 0,
            'treaty_portal' => !empty($node->field_treaty_portal[$node->language][0]['value']) ? $node->field_treaty_portal[$node->language][0]['value'] : 0,
            'iyff' => !empty($node->field_iyff[$node->language][0]['value']) ? $node->field_iyff[$node->language][0]['value'] : 0,
          );
          foreach ($translations as $language => $translation) {
            $ntranslation = entity_load_single('node', $translation->nid);
            $nwrapper = entity_metadata_wrapper('node', $ntranslation);
            $new_techs['technology'][] = array(
              'technologies_id' => $node->nid,
              'title' => $translation->title,
              'language' => $language,
              'created' => date('Y-m-d H:i:s', $ntranslation->created),
              'changed' => date('Y-m-d H:i:s', $ntranslation->changed),
              'summary' => $nwrapper->field_summary->value(),
              'description' => $nwrapper->field_description->value()['value'],
              'validation' => $nwrapper->field_validation->value()['value'],
              'further_reading' => $nwrapper->field_further_reading->value()['value'],
              'uid' => $ntranslation->uid
            );
          }
        }
        else {
          echo "Could not find tnid".$node->nid."\n";
        }
      }
      else {
        $new_techs['technologies'][] = array(
          'id' => $node->nid,
          'title_en' => $node->language == 'en' ? $node->title : '',
          'title_fr' => $node->language == 'fr' ? $node->title : '',
          'title_es' => $node->language == 'es' ? $node->title : '',
          'title_ar' => '',
          'title_cn' => '',
          'available_en' => $node->language == 'en' ? 1 : 0,
          'available_fr' => $node->language == 'fr' ? 1 : 0,
          'available_es' => $node->language == 'es' ? 1 : 0,
          'available_ar' => 0,
          'available_cn' => 0,
          'status' => $node->status,
          'incomplete' => !empty($node->field_technology_incomplete[$node->language][0]['value']) ? $node->field_technology_incomplete[$node->language][0]['value'] : 0,
          'treaty_portal' => !empty($node->field_treaty_portal[$node->language][0]['value']) ? $node->field_treaty_portal[$node->language][0]['value'] : 0,
          'iyff' => !empty($node->field_iyff[$node->language][0]['value']) ? $node->field_iyff[$node->language][0]['value'] : 0,
        );
        $new_techs['technology'][] = array(
          'technologies_id' => $node->nid,
          'title' => $node->title,
          'language' => $node->language,
          'created' => date('Y-m-d H:i:s', $node->created),
          'changed' => date('Y-m-d H:i:s', $node->changed),
          'summary' => $wrapper->field_summary->value(),
          'description' => $wrapper->field_description->value()['value'],
          'validation' => $wrapper->field_validation->value()['value'],
          'further_reading' => $wrapper->field_further_reading->value()['value'],
          'uid' => $node->uid
        );
      }
      // Categories
      foreach ($categories as $cat_id) {
        $new_techs['technologies_has_category'][] = array(
          'technologies_id' => $node->nid,
          'category_id' => $cat_id
        );
      }
      // Images
      foreach ($images as $img) {
        $dupe = false;
        foreach ($new_techs['technologies_has_image'] as $image) {
          if ($image['technologies_id'] == $node->nid && $image['file_id'] == $fid) {
            $dupe = true;
          }
        }
        if (!$dupe) {
          $new_techs['technologies_has_image'][] = array(
            'technologies_id' => $node->nid,
            'file_id' => $img['fid'],
            'caption' => $img['title']
          );
        }
      }
      // Keywords
      foreach ($keywords as $keyword) {
        $agrovoc_id = $all_keywords_agrovoc[$keyword];
        if ($all_keywords[$agrovoc_id]) {
          $dupe = false;
          foreach ($new_techs['technologies_has_keyword'] as $has_keyword) {
            if ($has_keyword['technologies_id'] == $node->nid && $has_keyword['keyword_id'] == $all_keywords[$agrovoc_id]) {
              $dupe = true;
            }
          }
          if (!$dupe) {
            $new_techs['technologies_has_keyword'][] = array(
              'technologies_id' => $node->nid,
              'keyword_id' => $all_keywords[$agrovoc_id]
            );
          }
        }
      }
      // Partners
      foreach ($sources as $nid) {
        if (!empty($nid)) {
          $new_techs['technologies_has_partner'][] = array(
            'technologies_id' => $node->nid,
            'partner_id' => $nid
          );
        }
      }
      // Regions
      foreach ($regions as $tid) {
        if (!empty($tid)) {
          $new_techs['technologies_has_region'][] = array(
            'technologies_id' => $node->nid,
            'region_id' => $tid
          );
        }
      }
      // Countries
      foreach ($countries as $iso2) {
        if (!empty($iso2)) {
          $new_techs['technologies_has_country'][] = array(
            'technologies_id' => $node->nid,
            'country_id' => $iso2
          );
        }
      }
      // Files
      foreach ($afiles as $fid) {
        if (!empty($fid)) {
          $new_techs['technologies_has_files'][] = array(
            'technologies_id' => $node->nid,
            'file_id' => $fid
          );
        }
      }
    }
  }

  return $new_techs;
}

/**
 * Migrate TECA categories
 */
function teca_migrate_categories() {
  // Load technologies
  $query = new EntityFieldQuery();

  $query->entityCondition('entity_type', 'taxonomy_term', '=')
    ->entityCondition('bundle', 'technology_categories');

  $result = $query->execute();

  $new_cats = [];
  foreach ($result['taxonomy_term'] as $record) {
    $term = entity_load_single('taxonomy_term', $record->tid);
    $new_cats[] = array(
      'id' => $term->tid,
      'name_en' => $term->name,
      'name_fr' => i18n_string_translate('taxonomy:term:'.$term->tid.':name', $term->name, array('langcode' => 'fr')),
      'name_es' => i18n_string_translate('taxonomy:term:'.$term->tid.':name', $term->name, array('langcode' => 'es'))
    );
  }

  return $new_cats;
}

/**
 * Migrate TECA regions
 */
function teca_migrate_regions() {
  // Load technologies
  $query = new EntityFieldQuery();

  $query->entityCondition('entity_type', 'taxonomy_term', '=')
    ->entityCondition('bundle', 'technology_regions');

  $result = $query->execute();

  $new_cats = [];
  foreach ($result['taxonomy_term'] as $record) {
    $term = entity_load_single('taxonomy_term', $record->tid);
    $new_cats[] = array(
      'id' => $term->tid,
      'name_en' => $term->name,
      'name_fr' => i18n_string_translate('taxonomy:term:'.$term->tid.':name', $term->name, array('langcode' => 'fr')),
      'name_es' => i18n_string_translate('taxonomy:term:'.$term->tid.':name', $term->name, array('langcode' => 'es'))
    );
  }

  return $new_cats;
}

/**
 * Migrate TECA keywords
 */
function teca_migrate_keywords() {
  // Load keywords
  $query = new EntityFieldQuery();

  $query->entityCondition('entity_type', 'taxonomy_term', '=')
    ->entityCondition('bundle', 'keywords');

  $result = $query->execute();

  $new_cats = [];
  $agrovoc_ids = array();
  foreach ($result['taxonomy_term'] as $record) {
    $term = entity_load_single('taxonomy_term', $record->tid);
    $ragrovoc = db_select('taxonomy_term_data', 't')
      ->fields('t')
      ->condition('tid', $term->tid)
      ->execute()
      ->fetchAssoc();
    if (!in_array($ragrovoc['agrovoc_id'], $agrovoc_ids) && !empty($ragrovoc['agrovoc_id'])) {
      $agrovoc_ids[] = $ragrovoc['agrovoc_id'];
      $translation_set = i18n_translation_set_load($term->i18n_tsid);
      $translations = array();
      if (!empty($translation_set)) {
        $translations = $translation_set->get_translations();
        $new_cats[] = array(
          'id' => $term->tid,
          'name_en' => isset($translations['en']) ? $translations['en']->name : '',
          'name_fr' => isset($translations['fr']) ? $translations['fr']->name : '',
          'name_es' => isset($translations['es']) ? $translations['es']->name : '',
          'agrovoc_id' => $ragrovoc['agrovoc_id']
        );
      }
      else {
        $new_cats[] = array(
          'id' => $term->tid,
          'name_en' => $term->language == 'en' ? $term->name : '',
          'name_fr' => $term->language == 'fr' ? $term->name : '',
          'name_es' => $term->language == 'es' ? $term->name : '',
          'agrovoc_id' => $ragrovoc['agrovoc_id']
        );
      } 
    }
  }

  return $new_cats;
}

/**
 * Migrate TECA files
 */
function teca_migrate_files() {
  // Load technologies
  $query = new EntityFieldQuery();

  $query->entityCondition('entity_type', 'file', '=');

  $result = $query->execute();

  $new_files = [];
  foreach ($result['file'] as $record) {
    $file = entity_load_single('file', $record->fid);
    if ($file->uid) {
      $new_files[] = array(
        'id' => $file->fid,
        'path' => str_replace('http://default', 'http://teca.fao.org', file_create_url($file->uri)),
        'uid' => $file->uid,
      );
    }
  }

  return $new_files;
}

function teca_migrate_sql($table, $values) {
  if (!empty($values)) {
    $sql = "INSERT INTO `$table` (`".implode("`, `", array_keys($values[0]))."`) VALUES ";
    $i = 0;
    foreach ($values as $value) {
      foreach ($value as &$val) {
        $val = mysql_escape_string($val);
        if (empty($val) && $val != 0) {
          $val = 'NULL';
        }
      }
      if ($i > 0) {
        $sql .= ", ";
      }
      $sql .= "('".implode("', '", $value)."')";
      $i++;
    }

    $sql .= ";";
    $sql = str_replace("'NULL'", "NULL", $sql);
  }
  return $sql;
}

/**
 * Migrate TECA exchange groups
 */
function teca_migrate_exchange_groups() {
  // Load users
  $query = new EntityFieldQuery();

  $query->entityCondition('entity_type', 'node', '=')
    ->entityCondition('bundle', 'exchange_group');

  $result = $query->execute();

  $new_groups = [];
  foreach ($result['node'] as $record) {
    $node = entity_load_single('node', $record->nid);
    $wrapper = entity_metadata_wrapper('node', $node);
    $icon = $wrapper->field_icon->value();
    $new_groups[] = array(
      'id' => $node->nid,
      'title' => $node->title,
      'uid' => $node->uid,
      'status' => $node->status,
      'created' => date('Y-m-d H:i:s', $node->created),
      'changed' => date('Y-m-d H:i:s', $node->changed),
      'description' => $wrapper->body->value()['value'],
      'language_id' => $node->language,
      'icon_id' => empty($icon['fid']) ? 'NULL': $icon['fid'],
    );
  }

  return $new_groups;
}

/**
 * Migrate TECA News
 */
function teca_migrate_news() {
  // Load users
  $query = new EntityFieldQuery();

  $query->entityCondition('entity_type', 'node', '=')
    ->entityCondition('bundle', 'article');

  $result = $query->execute();

  $new_news = [];
  foreach ($result['node'] as $record) {
    $node = entity_load_single('node', $record->nid);
    $wrapper = entity_metadata_wrapper('node', $node);
    $groups = $wrapper->og_group_ref->value();
    $group = $groups[0];
    if (!empty($group->nid)) {
      $new_news['news'][] = array(
        'id' => $node->nid,
        'title' => $node->title,
        'uid' => $node->uid,
        'status' => $node->status,
        'created' => date('Y-m-d H:i:s', $node->created),
        'changed' => date('Y-m-d H:i:s', $node->changed),
        'description' => $wrapper->body->value()['value'],
        'language_id' => $node->language,
        'group_id' => $group->nid
      );
      foreach ($wrapper->field_images->value() as $image) {
        $new_news['news_has_image'][] = array(
          'news_id' => $node->nid,
          'file_id' => $image['fid']
        );
      }
      foreach ($wrapper->field_attached_files->value() as $file) {
        $new_news['news_has_file'][] = array(
          'news_id' => $node->nid,
          'file_id' => $file['fid']
        );
      }
    }
  }

  return $new_news;
}

/**
 * Migrate TECA Resources
 */
function teca_migrate_resources() {
  // Load users
  $query = new EntityFieldQuery();

  $query->entityCondition('entity_type', 'node', '=')
    ->entityCondition('bundle', 'resource');

  $result = $query->execute();

  $new_resources = [];
  foreach ($result['node'] as $record) {
    $node = entity_load_single('node', $record->nid);
    $wrapper = entity_metadata_wrapper('node', $node);
    $groups = $wrapper->og_group_ref->value();
    $group = $groups[0];
    if (!empty($group->nid)) {
      $new_resources['resource'][] = array(
        'id' => $node->nid,
        'title' => $node->title,
        'uid' => $node->uid,
        'status' => $node->status,
        'created' => date('Y-m-d H:i:s', $node->created),
        'changed' => date('Y-m-d H:i:s', $node->changed),
        'description' => $wrapper->body->value()['value'],
        'language_id' => $node->language,
        'group_id' => $group->nid
      );
      foreach ($wrapper->field_attached_files->value() as $file) {
        $new_resources['resource_has_file'][] = array(
          'resource_id' => $node->nid,
          'file_id' => $file['fid']
        );
      }
    }
  }

  return $new_resources;
}

/**
 * Migrate TECA Events
 */
function teca_migrate_events() {
  // Load users
  $query = new EntityFieldQuery();

  $query->entityCondition('entity_type', 'node', '=')
    ->entityCondition('bundle', 'event');

  $result = $query->execute();

  $new_events = [];
  foreach ($result['node'] as $record) {
    $node = entity_load_single('node', $record->nid);
    $wrapper = entity_metadata_wrapper('node', $node);
    $groups = $wrapper->og_group_ref->value();
    $group = $groups[0];
    if (!empty($group->nid)) {
      $new_events['event'][] = array(
        'id' => $node->nid,
        'title' => $node->title,
        'uid' => $node->uid,
        'status' => $node->status,
        'created' => date('Y-m-d H:i:s', $node->created),
        'changed' => date('Y-m-d H:i:s', $node->changed),
        'date_start' => $node->field_event_date2[$node->language][0]['value'],
        'date_end' => $node->field_event_date2[$node->language][0]['value2'],
        'description' => $wrapper->body->value()['value'],
        'language_id' => $node->language,
        'group_id' => $group->nid,
        'country_id' => $wrapper->field_country->value()->iso2,
        'url' => $wrapper->field_url->value()['url'],
        'institution' => $node->field_institution[$node->language][0]['value'],
        'contact_name' => $node->field_contact_name[$node->language][0]['value'],
        'contact_address' => $node->field_contact_address[$node->language][0]['value'],
        'contact_email' => $node->field_event_email[$node->language][0]['value']
      );
      foreach ($wrapper->field_attached_files->value() as $file) {
        $new_events['event_has_file'][] = array(
          'event_id' => $node->nid,
          'file_id' => $file['fid']
        );
      }
    }
  }

  return $new_events;
}

/**
 * Migrate TECA magazines
 */
function teca_migrate_magazines() {
  // Load users
  $query = new EntityFieldQuery();

  $query->entityCondition('entity_type', 'node', '=')
    ->entityCondition('bundle', 'magazine');

  $result = $query->execute();

  $new_magazines = [];
  foreach ($result['node'] as $record) {
    $node = entity_load_single('node', $record->nid);
    $wrapper = entity_metadata_wrapper('node', $node);
    $groups = $wrapper->og_group_ref->value();
    $group = $groups[0];
    if (!empty($group->nid)) {
      $new_magazines[] = array(
        'id' => $node->nid,
        'title' => $node->title,
        'uid' => $node->uid,
        'status' => $node->status,
        'created' => date('Y-m-d H:i:s', $node->created),
        'changed' => date('Y-m-d H:i:s', $node->changed),
        'description' => $wrapper->body->value()['value'],
        'language_id' => $node->language,
        'group_id' => $group->nid,
        'type' => $wrapper->field_magazine_type->value()->name,
        'date' => empty($node->field_magazine_date[$node->language][0]['value']) ? 'NULL' : $node->field_magazine_date[$node->language][0]['value'],
        'toc_id' => empty($wrapper->field_table_of_contents->value()[0]['fid']) ? 'NULL' : $wrapper->field_table_of_contents->value()[0]['fid'],
        'cover_id' => empty($wrapper->field_cover_page->value()[0]['fid']) ? 'NULL' : $wrapper->field_cover_page->value()[0]['fid'],
        'notes_id' => empty($wrapper->field_mag_additional_information->value()[0]['fid']) ? 'NULL' : $wrapper->field_mag_additional_information->value()[0]['fid']
      );
    }
  }

  return $new_magazines;
}

/**
 * Migrate TECA discussions
 */
function teca_migrate_discussions() {
  // Load users
  $query = new EntityFieldQuery();

  $query->entityCondition('entity_type', 'node', '=')
    ->entityCondition('bundle', 'discussion');

  $result = $query->execute();

  $new_discussions = [];
  foreach ($result['node'] as $record) {
    $node = entity_load_single('node', $record->nid);
    $wrapper = entity_metadata_wrapper('node', $node);
    $groups = $wrapper->og_group_ref->value();
    $group = $groups[0];
    if (!empty($group->nid)) {
      $new_discussions['discussion'][] = array(
        'id' => $node->nid,
        'title' => $node->title,
        'uid' => $node->uid,
        'status' => $node->status,
        'created' => date('Y-m-d H:i:s', $node->created),
        'changed' => date('Y-m-d H:i:s', $node->changed),
        'description' => $wrapper->body->value()['value'],
        'language_id' => $node->language,
        'group_id' => $group->nid,
      );

      foreach ($wrapper->field_images->value() as $file) {
        $new_discussions['discussion_has_image'][] = array(
          'discussion_id' => $node->nid,
          'file_id' => $file['fid']
        );
      }

      foreach ($wrapper->field_attached_files->value() as $file) {
        $new_discussions['discussion_has_file'][] = array(
          'discussion_id' => $node->nid,
          'file_id' => $file['fid']
        );
      }
    }
  }


  return $new_discussions;
}

function teca_migrate() {
  date_default_timezone_set('UTC');
  echo "Migrating files\n";
  $files = teca_migrate_files();
  $sql = teca_migrate_sql('file', $files)."\n\n";
  echo "Migrating users\n";
  $users = teca_migrate_users();
  $sql .= teca_migrate_sql('users', $users)."\n\n";
  echo "Migrating regions\n";
  $regions = teca_migrate_regions();
  $sql .= teca_migrate_sql('region', $regions)."\n\n";
  echo "Migrating keywords\n";
  $keywords = teca_migrate_keywords();
  $sql .= teca_migrate_sql('keyword', $keywords)."\n\n";
  echo "Migrating categories\n";
  $categories = teca_migrate_categories();
  $sql .= teca_migrate_sql('category', $categories)."\n\n";
  echo "Migrating partners\n";
  $partners = teca_migrate_partners();
  $sql .= teca_migrate_sql('partner', $partners)."\n\n";
  echo "Migrating technologies\n";
  $technologies = teca_migrate_technologies($keywords);
  $sql .= teca_migrate_sql('technologies', $technologies['technologies'])."\n\n";
  $sql .= teca_migrate_sql('technology', $technologies['technology'])."\n\n";
  unset($technologies['technologies']);
  unset($technologies['technology']);
  foreach ($technologies as $table => $values) {
    $sql .= teca_migrate_sql($table, $values)."\n\n";
  }
  echo "Migrating exchange groups\n";
  $groups = teca_migrate_exchange_groups();
  $sql .= teca_migrate_sql('group', $groups)."\n\n";
  echo "Migrating news\n";
  $news = teca_migrate_news();
  $sql .= teca_migrate_sql('news', $news['news'])."\n\n";
  $sql .= teca_migrate_sql('news_has_image', $news['news_has_image'])."\n\n";
  $sql .= teca_migrate_sql('news_has_file', $news['news_has_image'])."\n\n";
  echo "Migrating resources\n";
  $resources = teca_migrate_resources();
  $sql .= teca_migrate_sql('resource', $resources['resource'])."\n\n";
  $sql .= teca_migrate_sql('resource_has_file', $resources['resource_has_file'])."\n\n";
  echo "Migrating events\n";
  $events = teca_migrate_events();
  $sql .= teca_migrate_sql('event', $events['event'])."\n\n";
  $sql .= teca_migrate_sql('event_has_file', $events['event_has_file'])."\n\n";
  echo "Migrating magazines\n";
  $magazines = teca_migrate_magazines();
  $sql .= teca_migrate_sql('magazine', $magazines)."\n\n";
  echo "Migrating discussions\n";
  $discussions = teca_migrate_discussions();
  $sql .= teca_migrate_sql('discussion', $discussions['discussion'])."\n\n";
  $sql .= teca_migrate_sql('discussion_has_image', $discussions['discussion_has_image'])."\n\n";
  $sql .= teca_migrate_sql('discussion_has_file', $discussions['discussion_has_file'])."\n\n";
  return $sql;
}

$sql = teca_migrate();
file_put_contents(realpath(dirname(__FILE__)).'/newteca.sql', $sql);
echo "end";

