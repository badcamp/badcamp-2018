<?php

namespace Drupal\badcamp_stripe_payment\Form;

use Drupal\badcamp_stripe_payment\Entity\StripePayment;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\flag\FlagServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\stripe_api\StripeApiService;

/**
 * Class StripePaymentRefundForm.
 */
class StripePaymentRefundForm extends FormBase {

  /**
   * Drupal\stripe_api\StripeApiService definition.
   *
   * @var \Drupal\stripe_api\StripeApiService
   */
  protected $stripeApi;

  /**
   * Drupal Flag Service
   *
   * @var \Drupal\flag\FlagServiceInterface
   */
  protected $flagService;

  /**
   * Class constructor.
   */
  public function __construct(StripeApiService $stripeApiService, FlagServiceInterface $flagService) {
    $this->stripeApi = $stripeApiService;
    $this->flagService = $flagService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      $container->get('stripe_api.stripe_api'),
      $container->get('flag')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'stripe_payment_refund';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, StripePayment $stripe_payment = NULL) {
    if (!is_null($stripe_payment) && $stripe_payment->get('refunded')->first()->getValue()['value'] != 0){
      drupal_set_message(t('Payment has already been refunded'), 'error');
      $redirect_response = $this->redirect('user.page');
      $redirect_response->send();
    }

    if(!is_null($stripe_payment->get('entity_id')->first())) {
      $node = $stripe_payment->get('entity_id')
        ->first()
        ->get('entity')
        ->getTarget()
        ->getValue();
      $form['message'] = [
        '#markup' => t('Are you sure you want to refund the payment for @title?', [
          '@title' => $node->label()
        ]),
        '#prefix' => '<div>',
        '#suffix' => '</div>',
      ];
    }

    $form['payment'] = [
      '#type' => 'value',
      '#value' => $stripe_payment
    ];

    $form['confirm'] = [
      '#type' => 'submit',
      '#value' => t('Refund My Payment')
    ];

    $form['cancel'] = [
      '#type' => 'submit',
      '#value' => t('Cancel'),
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
    if ($form_state->getValue('op')->getUntranslatedString() == $form_state->getValue('confirm')->getUntranslatedString()) {

      $payment = $form_state->getValue('payment');

      $amount = $payment->get('amount')->first()->getValue()['value'];
      $charge_id = $payment->get('stripe_transaction_id')->first()->getValue()['value'];
      $refund_params = [
        'amount'  => $amount,
        'charge'  => $charge_id,
      ];

      try {
        $refund = $this->stripeApi->callWithErrors('Refund', 'create', $refund_params);
        $payment->set('refunded', 1);
        if( $payment->getType() == 'training_registration') {
          $payment->set('field_training_reg_cancelled_dt', date("Y-m-d\Th:i:s"));
          $flag = $this->flagService->getFlagById('add_to_schedule');
          $entity = $payment->get('field_train_reg_rel_training')->first()->get('entity')->getTarget()->getValue();
          try {
            $this->flagService->unflag($flag, $entity);
          }
          catch (\LogicException $e) {}
        }
        $payment->save();
      }
      catch(\Exception $e){
        drupal_set_message($e->getMessage(), 'error');
      }
    }
  }

  /**
   * Checks access for a specific request.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   */
  public function refundPaymentAccess(AccountInterface $account, StripePayment $stripe_payment = NULL) {
    $has_permission = ($account->hasPermission('administer stripe payment entities') ||
      $account->hasPermission('refund all purchases') ||
      $account->hasPermission('refund own purchases'));
    $refunded = $stripe_payment->get('refunded')->first()->getValue()['value'];
    return AccessResult::allowedIf($has_permission && $refunded != 1);
  }
}
