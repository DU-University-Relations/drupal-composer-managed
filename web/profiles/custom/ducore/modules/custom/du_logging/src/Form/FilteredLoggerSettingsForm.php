<?php

namespace Drupal\du_logging\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\RfcLogLevel;

class FilteredLoggerSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['du_logging.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'du_logging_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('du_logging.settings');

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable filtered logging'),
      '#default_value' => $config->get('enabled'),
      '#description' => $this->t('When enabled, messages matching the configured filters will be suppressed.'),
    ];

    $form['log_levels'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Allowed log levels'),
      '#description' => $this->t('Only log messages with these severity levels. If none selected, all levels are logged.'),
      '#options' => [
        RfcLogLevel::EMERGENCY => $this->t('Emergency'),
        RfcLogLevel::ALERT => $this->t('Alert'),
        RfcLogLevel::CRITICAL => $this->t('Critical'),
        RfcLogLevel::ERROR => $this->t('Error'),
        RfcLogLevel::WARNING => $this->t('Warning'),
        RfcLogLevel::NOTICE => $this->t('Notice'),
        RfcLogLevel::INFO => $this->t('Info'),
        RfcLogLevel::DEBUG => $this->t('Debug'),
      ],
      '#default_value' => $config->get('log_levels') ?? [0, 1, 2, 3, 4, 5, 6, 7],
    ];

    $form['message_types'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message types to filter'),
      '#default_value' => implode("\n", $config->get('message_types') ?: []),
      '#description' => $this->t('Enter one message type (channel) per line. Example: php, page not found'),
    ];

    $form['patterns'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message patterns to filter'),
      '#default_value' => implode("\n", $config->get('patterns') ?: []),
      '#description' => $this->t('Enter one regex pattern per line. Example: /deprecated/i'
      . "\nPatterns are applied to the final, rendered log message (as shown in the database log UI), not the raw template string."),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $message_types = array_filter(array_map('trim', explode("\n", $form_state->getValue('message_types'))));
    $patterns = array_filter(array_map('trim', explode("\n", $form_state->getValue('patterns'))));

    // Filter out unchecked values (checkboxes return 0 for unchecked)
    $log_levels = array_filter($form_state->getValue('log_levels'));

    $this->config('du_logging.settings')
      ->set('enabled', $form_state->getValue('enabled'))
      ->set('log_levels', array_values($log_levels))
      ->set('message_types', $message_types)
      ->set('patterns', $patterns)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
