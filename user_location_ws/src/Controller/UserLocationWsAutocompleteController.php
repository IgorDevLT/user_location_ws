<?php

/**
 * @file
 * Contains \Drupal\user_location_ws\Controller\UserLocationWsAutocompleteController.
 */

namespace Drupal\user_location_ws\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Unicode;
use Drupal\field\Entity\FieldConfig;
use Drupal;
use Drupal\user_postit\UserPostitClient;

/**
 * Returns autocomplete responses for PostIt.
 */
class UserLocationWsAutocompleteController {

  /**
   * Returns response for the PostIt data name autocompletion.
   * DEPRECATED: was used as example on previous presentation.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object containing the search string.
   * @param string $entity_type
   *   The type of entity that owns the field.
   * @param string $bundle
   *   The name of the bundle that owns the field.
   * @param $field_name
   *   The name of the field.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions for PostIt.
   */
  public function autocomplete(Request $request, $entity_type, $bundle, $field_name) {
    // TODO: this is derecated now, was introduced as example of possible usage wih WS API calls.
    $matches = array();
    $string = $request->query->get('q');

    // Use PostIt call to validate.
    $result = \Drupal::service('user_location_ws_client')->random($string);

    foreach ($result['data'] as $item => $data) {
      $matches[] = array('value' => $data['postcode'], 'label' => $data['address']);
    }

    return new JsonResponse($matches);
  }
}
