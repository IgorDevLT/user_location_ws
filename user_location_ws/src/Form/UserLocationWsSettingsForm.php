<?php

namespace Drupal\user_location_ws\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines settings form for User Location module.
 */
class UserLocationWsSettingsForm extends ConfigFormBase {

  /**
   * Configuration name.
   */
  const CONFIG_NAME = 'user_location_ws.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_location_ws_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [static::CONFIG_NAME];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::CONFIG_NAME);
    $form['#tree'] = TRUE;

    $form['ws'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('WS settings'),
    ];

    $form['ws']['service_url'] = [
      '#type' => 'url',
      '#title' => $this->t('WS URL'),
      '#default_value' => $config->get('ws.service_url'),
    ];

    $form['ws']['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('WS API key'),
      '#default_value' => $config->get('ws.api_key'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config(static::CONFIG_NAME)
      ->set('ws', $form_state->getValue('ws'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
