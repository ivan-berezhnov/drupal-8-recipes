<?php

/**
 * @file
 * Post update hooks for example_module.
 */

use Drupal\media\Entity\Media;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Add image to new field prgf image for example_module.
 */
function example_module_post_update_example_module_image_field(&$sandbox) {

  // Init banch if we have a lot of items.
  if (!isset($sandbox['progress'])) {
    $sandbox['progress'] = 0;
    $sandbox['current'] = 0;
    $sandbox['max'] = \Drupal::entityQuery('paragraph') // Entity
      ->condition('type', ['example'], 'IN') // Entity type
      ->count()
      ->execute();
  }

  // Get all content ids by entity type.
  $paragraph_ids = \Drupal::entityQuery('paragraph') // Entity
    ->condition('type', ['example'], 'IN') // Entity type
    ->condition('id', $sandbox['current'], '>')
    ->range(0, 100)
    ->sort('id')
    ->execute();

  // Get entity object.
  $paragraphs = Paragraph::loadMultiple($paragraph_ids);

  foreach ($paragraphs as $paragraph) {

    // Get data from image field
    $file = $paragraph->get('field_image')->getValue();

    // Check if image field not empty.
    if (!isset($file[0]['target_id'])) {
      $sandbox['progress']++;
      $sandbox['current'] = $paragraph->id();
      continue;
    }

    // Get media with current fid.
    $media = \Drupal::entityQuery('media')
      ->condition('status', 1)
      ->condition('bundle', 'image')
      ->condition('field_media_image', $file[0]['target_id'])
      ->execute();

    // If media doesn't exist create new.
    if (empty($media)) {
      $media = Media::create([
        'bundle'           => 'image',
        'uid'              => \Drupal::currentUser()->id(),
        'field_media_image' => [
          'target_id' => $file[0]['target_id'],
        ],
      ]);

      $media->setName($file[0]['alt']);
      $media->setPublished(TRUE)->save();

      // Save media object to media field.
      $paragraph->set('field_media', $media->id())->save();
    }
    // If exist get it.
    else {
      $media = Media::loadMultiple($media);

      foreach ($media as $entity) {
        $mid = $entity->id();
      }

      // Save media object to media field.
      $paragraph->set('field_media', $mid)->save();
    }

    $sandbox['progress']++;
    $sandbox['current'] = $paragraph->id();
  }

  unset($paragraph, $media, $file);

  $sandbox['#finished'] = empty($sandbox['max']) ? 1 : ($sandbox['progress'] / $sandbox['max']);

  if (function_exists(  'drush_print')) {
    drush_print(dt('Finished: %items', ['%items' => $sandbox['#finished']]));
  }
}
