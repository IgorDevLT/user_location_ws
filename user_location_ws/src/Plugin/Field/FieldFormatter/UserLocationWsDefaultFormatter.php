<?php

namespace Drupal\user_location_ws\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal;

/**
 * Plugin implementation of the formatter.
 *
 * @FieldFormatter(
 *   id = "user_location_ws_default",
 *   module = "user_location_ws",
 *   label = @Translation("User Location default formatter"),
 *   field_types = {
 *     "user_location_ws"
 *   }
 * )
 */
class UserLocationWsDefaultFormatter extends FormatterBase {

  /**
   * @inheritDoc
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    /** @var \Drupal\Core\Field\FieldItemInterface $item */
    foreach ($items as $delta => $item) {

      if (!$item->isEmpty()) {
        $list = \Drupal::service('user_location_ws_client')->obtainData();
        $municipality = $list[$item->municipality];
        $element[$delta] = [
          '#markup' => "City: {$item->city}, Municipality: {$municipality}",
        ];
      }
    }

    return $element;
  }

}
