<?php

namespace Drupal\dms_instance\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'dms_instance.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dms_instance_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('dms_instance.settings');
    $form['instance_statuses'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Instance Statues'),
      '#default_value' => $config->get('instance_statuses'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('dms_instance.settings')
      ->set('instance_statuses', $form_state->getvalue('instance_statuses'))
      ->save();
  }

}
