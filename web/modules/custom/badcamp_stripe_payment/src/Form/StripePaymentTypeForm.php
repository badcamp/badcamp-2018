<?php

namespace Drupal\badcamp_stripe_payment\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class StripePaymentTypeForm.
 *
 * @package Drupal\badcamp_stripe_payment\Form
 */
class StripePaymentTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $stripe_payment_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $stripe_payment_type->label(),
      '#description' => $this->t("Label for the Stripe payment type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $stripe_payment_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\badcamp_stripe_payment\Entity\StripePaymentType::load',
      ],
      '#disabled' => !$stripe_payment_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $stripe_payment_type = $this->entity;
    $status = $stripe_payment_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Stripe payment type.', [
          '%label' => $stripe_payment_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Stripe payment type.', [
          '%label' => $stripe_payment_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($stripe_payment_type->toUrl('collection'));
  }

}
