<?php

namespace Drupal\badcamp_stripe_payment\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\stripe_api\StripeApiService;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Plugin implementation of the 'stripe_payment' formatter.
 *
 * @FieldFormatter(
 *   id = "stripe_payment",
 *   module = "badcamp_stripe_payment",
 *   label = @Translation("Simple stripe payment"),
 *   field_types = {
 *     "stripe_payment"
 *   }
 * )
 */
class StripePayment extends FormatterBase implements ContainerFactoryPluginInterface {
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
   * Array of Stripe Payment Bundles.
   *
   * @var array|mixed
   */
  protected $stripeBundles;

  /**
   * StripePayment constructor.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    StripeApiService $stripe_api,
    EntityTypeManager $entityManager,
    EntityTypeBundleInfo $bundleInfo,
    AccountProxy $account
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->stripeApi = $stripe_api;
    $this->entityTypeManager = $entityManager;
    $this->bundleInfo = $bundleInfo;
    $this->currentUser = $account;

    $this->stripeBundles = $this->bundleInfo->getBundleInfo('stripe_payment');
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
      $configuration['label'],
      $configuration['view_mode'],
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
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    if (count($items) > 0) {
      $entity = $items->getEntity();
      $total_payments = $this->getNumberOfPayments($entity->id(), $this->currentUser->id());
      $total_purchases = $this->getNumberOfPayments($entity->id());
      $item = $items->get(0);
      if (($total_payments < $item->max_payments || $item->max_payments == 0) && $item->enable && ($total_purchases < $item->max_purchases || $item->max_purchases == 0)) {
        $payment_label = $this->stripeBundles[$item->payment_type]['label'];
        $elements[] = [
          '#theme' => 'stripe_checkout',
          '#description' => $item->description_value,
          '#data_key' => $this->stripeApi->getPubKey(),
          '#amount' => $item->amount,
          "#email" => $this->currentUser->getEmail(),
          '#org_name' => $entity->label(),
          '#data_description' => $payment_label,
          '#data_zip_code' => $item->avs,
          '#payment_type' => $item->payment_type,
          '#button_label' => $item->button_label,
          '#entity_type' => $entity->getEntityType()->id(),
          '#entity_id' => $entity->id(),
        ];

        if( $item->max_purchases > 0) {
          $elements[] = [
            '#type' => 'markup',
            '#markup' => $this->t('@count Spaces Available', [ '@count' => ($item->max_purchases - $total_purchases) ])
          ];
        }
      }
      elseif (!$item->enable){
        $elements[] = [
          '#theme' => 'stripe_checkout',
          '#description' => $item->description_value,
          '#button_label' => $item->button_label,
          '#enabled' => FALSE,
          '#show_button' => $item->show_button,
          '#button_link' => $item->link_button,
        ];
      }
      elseif ($total_payments >= $item->max_payments) {
        if($this->canRefund() && $item->last_refund_date > date('Y-m-d')) {
          $payment = $this->getPaymentsForNode($entity->id(), $this->currentUser->id());
          $payment_id = array_pop($payment);
          $url = Url::fromRoute('badcamp_stripe_payment.refund', ['stripe_payment' => $payment_id], [
            'query' => [
              'destination' => Url::fromRoute('entity.node.canonical', [
                'node' => $items->getEntity()
                  ->id()
              ])->getInternalPath()
            ]
          ]);
          $link = Link::fromTextAndUrl(t('Refund Payment'), $url)
            ->toRenderable();
          $link['#attributes']['class'][] = 'button';
          $link['#attributes']['class'][] = 'large';
          $elements[] = [
            '#markup' => render($link)
          ];
        }
        else {
          $elements[] = [
            '#markup' => t('Paid!'),
            '#prefix' => '<div class="paid-message">',
            '#suffix' => '</div>'
          ];
        }
      }
      elseif ($total_purchases >= $item->max_purchases){
        $elements[] = [
          '#markup' => t('Sold Out'),
        ];
      }
    }
    $elements['#cache']['max-age'] = 0;
    return $elements;
  }

  /**
   * Get the number of payments for the provided entity.
   * Check also to see how many are for a particular user.
   */
  private function getNumberOfPayments($entity_id, $user_id = NULL) {
    $query = $this
      ->entityTypeManager
      ->getStorage('stripe_payment')
      ->getQuery('AND')
      ->condition('entity_id', $entity_id);

    if(!is_null($user_id)) {
      $query->condition('user_id', $user_id);
    }

    return $query->condition('refunded', 0)
      ->count()
      ->execute();
  }

  /**
   * Returns list of Payments for entity
   */
  private function getPaymentsForNode($entity_id, $user_id = NULL) {
    $query = $this
      ->entityTypeManager
      ->getStorage('stripe_payment')
      ->getQuery('AND')
      ->condition('entity_id', $entity_id);

    if(!is_null($user_id)) {
      $query->condition('user_id', $user_id);
    }

    return $query->condition('refunded', 0)
      ->execute();
  }

  /**
   * Returns if account can refund
   */
  private function canRefund() {
    $has_permission = ($this->currentUser->hasPermission('administer stripe payment entities') ||
      $this->currentUser->hasPermission('refund all purchases') ||
      $this->currentUser->hasPermission('refund own purchases'));

    return $has_permission;
  }

}
