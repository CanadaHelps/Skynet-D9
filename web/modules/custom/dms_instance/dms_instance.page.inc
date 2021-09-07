<?php

/**
 * @file
 * Contains dms_instance.page.inc.
 *
 * Page callback for DMS Instance entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for DMS Instance templates.
 *
 * Default template: dms_instance.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_dms_instance(array &$variables) {
  // Fetch DmsInstanceEntity Entity Object.
  $dms_instance = $variables['elements']['#dms_instance'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
