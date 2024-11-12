<?php

namespace Drupal\csc_social_feeds\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class CscSocialFeedsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['csc_social_feeds.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'csc_social_feeds_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('csc_social_feeds.settings');

    $form['fb_app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Facebook App ID'),
      '#default_value' => $config->get('fb_app_id'),
      '#required' => TRUE,
      '#description' => $this->t('The Developers Facebook App ID.'),
    ];

    $form['fb_app_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Facebook App Secret'),
      '#default_value' => $config->get('fb_app_secret'),
      '#required' => TRUE,
      '#description' => $this->t('The Developers Facebook App Secret.'),
    ];

    $form['fb_app_access_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Facebook App Access Token'),
      '#default_value' => $config->get('fb_app_access_token'),
      '#description' => $this->t('Initial Facebook Access Token. Can be either short or long term.'),
      '#maxlength' => 512,
    ];

    $form['fb_csc_page_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Facebook Feed CSC Page ID'),
      '#default_value' => $config->get('fb_csc_page_id'),
      '#description' => $this->t('CSC Page ID whose posts will feed the block.'),
    ];

    $rfts = $config->get('fb_app_token_refresh_ts');
    $rfstr = (empty($rfts)) ? 'Not set' : csc_social_date($rfts);
    $form['fb_app_token_refresh_ts'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Facebook App Token Last Refresh'),
      '#disabled' => TRUE, // Uneditable
      '#attributes' => [
        'style' => 'display:none;',
      ],
      '#suffix' => "<div>$rfstr</div>"
    ];

    $expdt = $config->get('fb_app_token_expires');
    $expdtstr = (empty($expdt)) ? 'Not set' : csc_social_date($expdt);
    $form['fb_app_token_expires'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Facebook App Token Expires'),
      '#disabled' => TRUE, // Uneditable
      '#attributes' => [
        'style' => 'display:none;',
      ],
      '#suffix' => "<div>$expdtstr</div>"
    ];

    $form['actions']['token_refresh'] = [
      '#type' => 'submit',
      '#value' => $this->t('Refresh Token'),
      '#weight' => 1,
      '#submit' => ['::refreshToken'],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $rfts = $form_state->getValue('fb_app_token_refresh_ts');
    if (empty($rfts) || strtolower($rfts) == "now") { $rfts = time(); }
    $this->configFactory->getEditable('csc_social_feeds.settings')
      ->set('fb_app_id', $form_state->getValue('fb_app_id'))
      ->set('fb_app_secret', $form_state->getValue('fb_app_secret'))
      ->set('fb_app_access_token', $form_state->getValue('fb_app_access_token'))
      ->set('fb_csc_page_id', $form_state->getValue('fb_csc_page_id'))
      ->save();
    parent::submitForm($form, $form_state);
    csc_social_feeds_update_token(); // Once info is there, immediately refresh token in case it is a short term one?
    csc_social_feeds_get_posts();
  }

  public function refreshToken(array &$form, FormStateInterface $form_state) {
    csc_social_feeds_update_token(true, true);
  }
}

