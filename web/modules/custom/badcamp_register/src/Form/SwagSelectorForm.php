<?php

namespace Drupal\badcamp_register\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\field\Entity\FieldStorageConfig;

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

  /** @var \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler */
  protected $moduleHandler;

  public function __construct(EntityTypeManagerInterface $entityTypeManager, ModuleHandlerInterface $moduleHandler) {
    $this->entityTypeManager = $entityTypeManager;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return void|static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('module_handler')
    );
  }

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
      $redirect_response = $this->redirect('badcamp_register.page_2');
      $redirect_response->send();
    }

    $amount = ((int)$payment->get('amount')->getString()) / 100;

    if ($amount >= 25) {
      $field_storage = FieldStorageConfig::loadByName('stripe_payment', 'field_t_shirt_size');
      $options = $field_storage->getSettings()['allowed_values'];

      $form['t_shirt_size'] = [
        '#type' => 'select',
        '#title' => $this->t('T-Shirt Size'),
        '#options' => $options,
        '#size' => 1,
        '#required' => TRUE,
        '#default_value' => ($payment->get('field_t_shirt_size')->getString()) ? $payment->get('field_t_shirt_size')->getString() : '',
      ];
    }

    if ($amount >= 100) {
      $field_storage = FieldStorageConfig::loadByName('stripe_payment', 'field_hoodie_size');
      $options = $field_storage->getSettings()['allowed_values'];
      $form['hoodie_size'] = [
        '#type' => 'select',
        '#title' => $this->t('Hoodie Size'),
        '#options' => $options,
        '#size' => 1,
        '#required' => TRUE,
        '#default_value' => ($payment->get('field_hoodie_size')->getString()) ? $payment->get('field_hoodie_size')->getString() : '',
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
    $payment->set('field_t_shirt_size', $form_state->getValue('t_shirt_size'));
    $payment->set('field_hoodie_size', $form_state->getValue('hoodie_size'));
    $payment->save();

    $config = $this->config('badcamp_register.settings')->get('page_2_swag');
    $route = $config['submit_redirect_route'];
    $this->moduleHandler->alter('badcamp_swag_selector_form_complete', $route, $payment);
    $redirect_response = $this->redirect($route);
    $redirect_response->send();
  }

  /**
   * Retrieve the latest stripe payment found for a given user id.
   * @param $uid
   * @return \Drupal\badcamp_stripe_payment\Entity\StripePayment
   */
  public function getLatestPaymentByUser($uid) {
    $payments = $this->entityTypeManager->getStorage('stripe_payment')->loadByProperties(['user_id' => $uid, 'type' => 'individual_sponsorship']);
    $payments = array_reverse($payments);
    foreach($payments as $payment) {
      return $payment;
    }
  }
}
