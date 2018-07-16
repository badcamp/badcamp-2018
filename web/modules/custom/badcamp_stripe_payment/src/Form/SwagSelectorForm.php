<?php

namespace Drupal\badcamp_stripe_payment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Class SwagSelectorForm.
 */
class SwagSelectorForm extends FormBase {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'swag_selector_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $current_uid = $this->currentUser()->id();
    $payment = $this->getLatestPaymentByUser($current_uid);

    if (!isset($payment)) {
      $redirect_response = $this->redirect('user.page');
      $redirect_response->send();
    }

    //@todo config to define levels instead of hardcoded
    $level = $this->_getLevel((int)$payment->get('amount')->getString());

    $form['message'] =[
      '#type' => 'item',
      '#title' => t('Thank you'),
      '#description' => t('Because of your generosity you are being rewarded 
      with magnificent SWAG! Please choose your size as this will help BADCamp 
      with orders and we will do our best to make sure you get the size you 
      select. Thank you!'),
    ];

    if ($level >= 1) {
      $form['t_shirt_type'] = [
        '#type' => 'select',
        '#title' => $this->t('T-Shirt Type'),
        '#options' => ["Women's" => $this->t("Women's"), "Men's" => $this->t("Men's")],
        '#size' => 1,
        '#default_value' => ($payment->get('field_t_shirt_type')->getString())? $payment->get('field_t_shirt_type')->getString() : '',
      ];
      $form['t_shirt_size'] = [
        '#type' => 'select',
        '#title' => $this->t('T-Shirt Size'),
        '#options' => ['X-Small' => $this->t('X-Small'), 'Small' => $this->t('Small'), 'Medium' => $this->t('Medium'), 'Large' => $this->t('Large'), 'X-Large' => $this->t('X-Large'), 'XX-Large' => $this->t('XX-Large'), 'XXX-Large' => $this->t('XXX-Large')],
        '#size' => 1,
        '#default_value' => ($payment->get('field_t_shirt_size')->getString())? $payment->get('field_t_shirt_size')->getString() : '',
      ];
    }

    if ($level >= 2) {
      $form['hoodie_size'] = [
        '#type' => 'select',
        '#title' => $this->t('Hoodie Size'),
        '#options' => ['Small' => $this->t('Small'), 'Medium' => $this->t('Medium'), 'Large' => $this->t('Large'), 'X-Large' => $this->t('X-Large'), 'XX-Large' => $this->t('XX-Large'), 'XX-Large' => $this->t('XX-Large'), 'XXX-Large' => $this->t('XXX-Large')],
        '#size' => 1,
        '#default_value' => ($payment->get('field_hoodie_size')->getString())? $payment->get('field_hoodie_size')->getString() : '',
      ];
    }

    $form['save'] = [
      '#type' => 'submit',
      '#title' => $this->t('Save'),
      '#value' => t('Save')
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $current_uid = $this->currentUser()->id();
    $payment = $this->getLatestPaymentByUser($current_uid);
    $payment->set('field_t_shirt_type',$form_state->getValue('t_shirt_type'));
    $payment->set('field_t_shirt_size',$form_state->getValue('t_shirt_size'));
    $payment->set('field_hoodie_size',$form_state->getValue('hoodie_size'));
    $payment->save();

    $redirect_response = $this->redirect('badcamp_stripe_payment.donation_complete');
    $redirect_response->send();
  }

  /**
   * Retrieve the latest stripe payment found for a given user id.
   * @param $uid
   * @return \Drupal\badcamp_stripe_payment\Entity\StripePayment
   */
  public function getLatestPaymentByUser($uid) {
    $this->entityTypeManager = \Drupal::service('entity_type.manager');
    $payments = $this->entityTypeManager->getStorage('stripe_payment')->loadByProperties(['user_id' => $uid, 'type' => 'donation']);
    $payments = array_reverse($payments);
    foreach($payments as $payment) {
      return $payment;
    }
  }

  protected function _getLevel($donation) {
    if ($donation > 5000) {
      return 2;
    }
    return 1;
  }

}
