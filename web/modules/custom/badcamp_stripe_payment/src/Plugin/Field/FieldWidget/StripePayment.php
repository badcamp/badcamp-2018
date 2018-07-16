<?php

namespace Drupal\badcamp_stripe_payment\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\stripe_api\StripeApiService;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Plugin implementation of the 'stripe_payment' widget.
 *
 * @FieldWidget(
 *   id = "stripe_payment",
 *   module = "badcamp_stripe_payment",
 *   label = @Translation("Stripe Payment"),
 *   field_types = {
 *     "stripe_payment"
 *   }
 * )
 */
class StripePayment extends WidgetBase implements ContainerFactoryPluginInterface {
  /**
   * Drupal\stripe_api\StripeApiService definition.
   *
   * @var \Drupal\stripe_api\StripeApiService
   */
  protected $stripeApi;
  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * Drupal\Core\Entity\EntityTypeBundleInfo definition.
   *
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
   * Constructor.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    array $third_party_settings,
    StripeApiService $stripe_api,
    EntityTypeManager $entityManager,
    EntityTypeBundleInfo $bundleInfo,
    AccountProxy $account
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
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
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('stripe_api.stripe_api'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['#theme_wrappers'][] = 'fieldset';

    $element['enable'] = [
      '#title' => t('Enable Payment For Field'),
      '#type' => 'checkbox',
      '#default_value' => isset($items[$delta]->enable) ? $items[$delta]->enable : 0,
    ];

    $element['show_button'] = [
      '#title' => t('Show Button'),
      '#type' => 'checkbox',
      '#default_value' => isset($items[$delta]->show_button) ? $items[$delta]->show_button : 0,
      '#description' => $this->t('Show button even when payment is not enabled.'),
    ];

    $element['avs'] = [
      '#title' => t('Turn on street and zip/postal code checks (address verification or AVS)?'),
      '#type' => 'checkbox',
      '#description' => t('Address verification checks (also known as Address Verification System or AVS) provide additional levels of confirmation that the person using the card is the legitimate owner of the card, useful to help identify and avoid fraud.'),
      '#default_value' => isset($items[$delta]->avs) ? $items[$delta]->avs : 0,
    ];

    $element['amount'] = [
      '#type' => 'number',
      '#title' => t('Amount'),
      '#description' => t('ONLY NUMBERS ARE ALLOWED! The dollar amount that should be charged IN CENTS, e.g:($20 = 2000)'),
      '#default_value' => isset($items[$delta]->amount) ? $items[$delta]->amount : 0,
    ];

    $element['max_payments'] = [
      '#type' => 'number',
      '#title' => t('Max # of Payments'),
      '#description' => t('How many times can someone try to purchase this item. 0 = Unlimited.'),
      '#default_value' => isset($items[$delta]->max_payments) ? $items[$delta]->max_payments : 0,
    ];

    $element['max_purchases'] = [
      '#type' => 'number',
      '#title' => t('Max # of Purchases'),
      '#description' => t('Total number of times everyone can purchase combined. 0 = Unlimited.'),
      '#default_value' => isset($items[$delta]->max_purchases) ? $items[$delta]->max_purchases : 0,
    ];

    $element['button_label'] = [
      '#type' => 'textfield',
      '#title' => t('Button Label'),
      '#description' => t('This is the label of the button that a user will click'),
      '#default_value' => isset($items[$delta]->button_label) ? $items[$delta]->button_label : '',
    ];

    $element['link_button'] = [
      '#title' => t('Button Link'),
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->link_button) ? $items[$delta]->link_button : '',
      '#description' => $this->t('Show button even when payment is not enabled.'),
    ];

    $element['description'] = [
      '#type' => 'text_format',
      '#title' => t('Description'),
      '#description' => t('The description of the item you are asking the buyer to pay for.'),
      '#default_value' => isset($items[$delta]->description_value) ? $items[$delta]->description_value : '',
      '#format' => isset($items[$delta]->description_format) ? $items[$delta]->description_format : 'basic_html',
    ];

    $element['last_refund_date'] = [
      '#title' => t('Last Refund Date'),
      '#type' => 'date',
      '#default_value' => isset($items[$delta]->last_refund_date) ? $items[$delta]->last_refund_date : '',
      '#date_increment' => 1,
      '#date_timezone' => drupal_get_user_timezone(),
    ];

    if ($definitions = $this->bundleInfo->getBundleInfo('stripe_payment')) {
      $bundle_options = [];
      if (isset($definitions) && is_array($definitions)) {
        foreach ($definitions as $definition_key => $definition) {
          $bundle_options[$definition_key] = $definition['label'];
        }
      }

      $element['payment_type'] = [
        '#type' => 'select',
        '#title' => t('Type'),
        '#description' => t('The type of payment'),
        '#required' => TRUE,
        '#options' => $bundle_options,
        '#default_value' => isset($items[$delta]->payment_type) ? $items[$delta]->payment_type : '',
      ];

      return $element;

    }
    else {
      drupal_set_message(t('You need to specify a payment type first'), 'error');
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // TODO: Change the autogenerated stub.
    $values = parent::massageFormValues($values, $form, $form_state);

    $new_values = $values;
    foreach ($values as $k => $v) {
      $new_values[$k]['description_value'] = $values[$k]['description']['value'];
      $new_values[$k]['description_format'] = $values[$k]['description']['format'];
    }
    return $new_values;
  }

}
