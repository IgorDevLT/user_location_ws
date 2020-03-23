<?php

namespace Drupal\user_location_ws\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user_location_ws\Exception\MunicipalityProviderException;
use Drupal\user_location_ws\Exception\MunicipalityProviderExceptionMissingKey;

/**
 * Plugin implementation of the 'User Location WS' field type.
 *
 * @FieldType(
 *   id = "user_location_ws",
 *   label = @Translation("User location ws"),
 *   description = @Translation("Stores user data from WS."),
 *   default_widget = "user_location_ws_default_select",
 *   default_formatter = "user_location_ws_default"
 * )
 */
class UserLocationWs extends FieldItemBase {

  const USER_LOCATION_WS_MAXLENGTH = 100;

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['municipality'] = DataDefinition::create('string')
      ->setLabel(t('Municipality'));

    $properties['city'] = DataDefinition::create('string')
      ->setLabel(t('City'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'municipality' => [
          'type' => 'char',
          'length' => static::USER_LOCATION_WS_MAXLENGTH,
          'not null' => FALSE,
        ],
        'city' => [
          'type' => 'char',
          'length' => static::USER_LOCATION_WS_MAXLENGTH,
          'not null' => FALSE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('municipality')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraint_manager = \Drupal::typedDataManager()
      ->getValidationConstraintManager();
    $constraints = parent::getConstraints();

    $constraints[] = $constraint_manager->create('ComplexData', [
      'municipality' => [
        'Length' => [
          'max' => static::USER_LOCATION_WS_MAXLENGTH,
          'maxMessage' => t('%name: data cannot be longer than @max characters.', [
            '%name' => $this->getFieldDefinition()
              ->getLabel(),
            '@max' => static::USER_LOCATION_WS_MAXLENGTH,
          ]),
        ],
      ],
    ]);

    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    $settings = $this->getSettings();
    static::defaultUserLocationWsForm($element, $settings);
    $element['municipality_select']['#description'] = t("Select at least one here");
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
        'municipality_select' => [],
      ] + parent::defaultFieldSettings();
  }

  /**
   * Builds municipality element available selections for admin.
   *
   * @param array $element
   *   The form associative array passed by reference.
   * @param array $settings
   *   The field settings array.
   */
  protected function defaultUserLocationWsForm(array &$element, array $settings) {
    $list = [];
    // TODO: inject from service.
    try {
      $list = \Drupal::service('user_location_ws_client')->obtainData();
    } catch (MunicipalityProviderException $e) {
      \Drupal::service('messenger')
        ->addError($this->t('Failed to fetch municipalities.'));
    } catch (MunicipalityProviderExceptionMissingKey $e) {
      \Drupal::service('messenger')->addError($this->t('Missing WS API key.'));
    }

    $element['municipality_select'] = [
      '#type' => 'select',
      '#title' => t('Selectable'),
      '#default_value' => $settings['municipality_select'],
      '#options' => $list,
      '#description' => t('Select all you want to make available for this field.'),
      '#multiple' => TRUE,
      '#size' => 10,
    ];
  }

}
