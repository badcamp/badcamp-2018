<?php

namespace Drupal\badcamp_stripe_payment\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a 'SkipDonationBlock' block.
 *
 * @Block(
 *  id = "skip_donation_block",
 *  admin_label = @Translation("Skip donation block"),
 * )
 */
class SkipDonationBlock extends BlockBase {


  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
         'path' => $this->t(''),
        ] + parent::defaultConfiguration();

 }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form['button_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button Text'),
      '#description' => $this->t('The button label'),
      '#default_value' => $this->configuration['button_text'],
      '#maxlength' => 64,
      '#size' => 64,
      '#weight' => '0',
    ];

    $form['path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path'),
      '#description' => $this->t('This is the internal path where you want the visitor to go when 
      they click the Skip button.'),
      '#default_value' => $this->configuration['path'],
      '#maxlength' => 255,
      '#size' => 64,
      '#weight' => '1',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['path'] = $form_state->getValue('path');
    $this->configuration['button_text'] = $form_state->getValue('button_text');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $build['skip_do_not_bug'] = [
      '#title' => $this->t('Do not bug me with this again'),
      '#type' => 'link',
      '#attributes' => [
        'class' => ['button', 'large']
      ],
      '#url' => URL::fromRoute('badcamp_stripe_payment.do_not_bug'),
      '#weight' => 1
    ];

    $build['skip_donation_block_path'] = [
      '#title' => $this->t($this->configuration['button_text']),
      '#type' => 'link',
      '#attributes' => [
        'class' => ['button', 'large', 'hollow']
      ],
      '#url' => URL::fromUserInput($this->configuration['path']),
      '#weight' => 1
    ];

    // disable caching for this block.
    $build['#cache']['max-age'] = 0;

    return $build;
  }

}
