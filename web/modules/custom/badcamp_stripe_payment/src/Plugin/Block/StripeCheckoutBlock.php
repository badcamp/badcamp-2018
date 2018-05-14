<?php

namespace Drupal\badcamp_stripe_payment\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\stripe_api\StripeApiService;
use Drupal\Core\Session\AccountProxy;

/**
 * Provides a 'StripeCheckoutBlock' block.
 *
 * @Block(
 *  id = "stripe_checkout_block",
 *  admin_label = @Translation("Stripe checkout block"),
 * )
 */
class StripeCheckoutBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\stripe_api\StripeApiService definition.
   *
   * @var \Drupal\stripe_api\StripeApiService
   */
  protected $stripeApi;
  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * @var \Drupal\Core\Entity\EntityTypeBundleInfo
   */
  protected $bundleInfo;
  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    StripeApiService $stripe_api,
    EntityTypeManager $entityManager,
    EntityTypeBundleInfo $bundleInfo,
    AccountProxy $account
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->stripeApi = $stripe_api;
    $this->entityTypeManager = $entityManager;
    $this->bundleInfo = $bundleInfo;
    $this->currentUser = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('stripe_api.stripe_api'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
         'turn_on_avs' => 1,
        ] + parent::defaultConfiguration();

 }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['turn_on_avs'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Turn on street and zip/postal code checks 
      (address verification or AVS)?'),
      '#description' => $this->t('Address verification checks (also known as 
      Address Verification System or AVS) provide additional levels of 
      confirmation that the person using the card is the legitimate owner of 
      the card, useful to help identify and avoid fraud.'),
      '#default_value' => isset($this->configuration['turn_on_avs'])? $this->configuration['turn_on_avs'] : 1,
      '#weight' => '1',
    ];

    $form['amount'] = [
      '#type' => 'number',
      '#required' => TRUE,
      '#title' => $this->t('Amount'),
      '#description' => $this->t('ONLY NUMBERS ARE ALLOWED! The dollar amount 
      that should be charged IN CENTS, e.g:($20 = 2000)'),
      '#default_value' => isset($this->configuration['amount'])? $this->configuration['amount']: '',
      '#weight' => '2',
    ];

    $form['description'] = array(
      '#type' => 'text_format',
      '#required' => FALSE,
      '#title' => $this->t('Description'),
      '#description' => $this->t('The description of the item you are asking 
      the buyer to pay for.'),
      '#default_value' => isset($this->configuration['description']['value'])?
        $this->configuration['description']['value']: '',
      '#format' => isset($this->configuration['description']['format'])?
        $this->configuration['description']['format'] : 'basic_html',
      '#weight' => '3'
    );

    if ($definitions = $this->bundleInfo->getBundleInfo('stripe_payment')) {
      $bundle_options = [];
      if (isset($definitions) && is_array($definitions)) {
        foreach ($definitions as $definition_key => $definition) {
          $bundle_options[$definition_key]  = $definition['label'];
        }
      }
      $form['payment_type'] = [
        '#type' => 'select',
        '#required' => TRUE,
        '#title' => $this->t('Type'),
        '#description' => $this->t('The type of payment.'),
        '#default_value' => isset($this->configuration['payment_type'])?
          $this->configuration['payment_type'] : '',
        '#options' => $bundle_options,
        '#weight' => '4',
      ];

      return $form;
    }
    else {
      drupal_set_message(t('You need to specify a payment type first'), 'error');
    }

  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['turn_on_avs'] = $form_state->getValue('turn_on_avs');
    $this->configuration['amount'] = $form_state->getValue('amount');
    $this->configuration['description'] = $form_state->getValue('description');
    $this->configuration['payment_type'] = $form_state->getValue('payment_type');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $build['stripe_checkout'] = [
      '#theme' => 'stripe_checkout',
      '#description' => $this->configuration['description']['value'],
      '#data_key' => $this->stripeApi->getPubKey(),
      '#amount' => $this->configuration['amount'],
      "#email" => $this->currentUser->getEmail(),
      '#org_name' => $this->label(),
      '#data_description' => $this->configuration['payment_type'],
      '#data_zip_code' => $this->configuration['turn_on_avs'],
      '#payment_type' => $this->configuration['payment_type']
    ];
    $build['#cache']['max-age'] = 0;
    return $build;
  }

}
