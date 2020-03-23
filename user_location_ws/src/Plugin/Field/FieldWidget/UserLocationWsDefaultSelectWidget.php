<?php

namespace Drupal\user_location_ws\Plugin\Field\FieldWidget;

use Drupal;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user_location_ws\Exception\MunicipalityProviderException;
use Drupal\user_location_ws\Exception\MunicipalityProviderExceptionMissingKey;

/**
 * Plugin implementation of the widget.
 *
 * @FieldWidget(
 *   id = "user_location_ws_default_select",
 *   label = @Translation("User location WS defaul select widget"),
 *   field_types = {
 *     "user_location_ws"
 *   }
 * )
 */
class UserLocationWsDefaultSelectWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    // Basic exampe of possible settings to be used.
    return [
        'size' => '60',
        'autocomplete_route_name' => 'user_location_ws.autocomplete',
        'placeholder' => t('Start typing a name ...'),
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $all_municipalities = [];
    try {
      // TODO: inject via container : $municipalities = $this->municipalityProvider->fetch();
      $all_municipalities = \Drupal::service('user_location_ws_client')
        ->obtainData();
    } catch (MunicipalityProviderException $e) {
      $this->messenger()->addError($this->t('Failed to fetch municipalities.'));
    } catch (MunicipalityProviderExceptionMissingKey $e) {
      $this->messenger()->addError($this->t('Missing WS API key.'));
    }

    // Admin has last chance of blocking some municipalities.
    $allowed_by_admin = $all_municipalities;

    if (!empty($this->getFieldSetting('municipality_select'))) {
      $allowed_by_admin = array_intersect_key($all_municipalities, $this->getFieldSetting('municipality_select'));
    }

    // Municipality element.
    $element['municipality'] = [
      '#type' => 'select',
      '#title' => $this->t('Municipality'),
      '#empty_option' => $this->t('- Select -'),
      '#options' => $allowed_by_admin,
      '#default_value' => $items[$delta]->municipality ?? NULL,
      '#required' => TRUE,
    ];

    // City element: conditional if previous selected.
    $element['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#default_value' => $items[$delta]->city ?? NULL,
      '#states' => [
        'visible' => [
          'select[name="field_user_location_ws[' . $delta . '][municipality]"]' => ['!value' => ''],
        ],
      ],
      '#required' => TRUE,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    // Example of usage of additional params and settings.
    $element = parent::settingsForm($form, $form_state);
    $element['size'] = [
      '#type' => 'number',
      '#title' => t('Size'),
      '#default_value' => $this->getSetting('size'),
      '#required' => TRUE,
      '#min' => 20,
    ];
    $element['placeholder'] = [
      '#type' => 'textfield',
      '#title' => t('Placeholder'),
      '#default_value' => $this->getSetting('placeholder'),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    // Example of usage of additional params and settings.
    $summary = [];
    $summary[] = t('Size: @size', ['@size' => $this->getSetting('size')]);
    $summary[] = t('Placeholder: @placeholder', ['@placeholder' => $this->getSetting('placeholder')]);
    return $summary;
  }

}
