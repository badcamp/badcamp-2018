<?php

namespace Drupal\badcamp_stripe_payment\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Stripe payment type entity.
 *
 * @ConfigEntityType(
 *   id = "stripe_payment_type",
 *   label = @Translation("Stripe payment type"),
 *   handlers = {
 *     "list_builder" = "Drupal\badcamp_stripe_payment\StripePaymentTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\badcamp_stripe_payment\Form\StripePaymentTypeForm",
 *       "edit" = "Drupal\badcamp_stripe_payment\Form\StripePaymentTypeForm",
 *       "delete" = "Drupal\badcamp_stripe_payment\Form\StripePaymentTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\badcamp_stripe_payment\StripePaymentTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "stripe_payment_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "stripe_payment",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/stripe_payment_type/{stripe_payment_type}",
 *     "add-form" = "/admin/structure/stripe_payment_type/add",
 *     "edit-form" = "/admin/structure/stripe_payment_type/{stripe_payment_type}/edit",
 *     "delete-form" = "/admin/structure/stripe_payment_type/{stripe_payment_type}/delete",
 *     "collection" = "/admin/structure/stripe_payment_type"
 *   }
 * )
 */
class StripePaymentType extends ConfigEntityBundleBase implements StripePaymentTypeInterface {

  /**
   * The Stripe payment type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Stripe payment type label.
   *
   * @var string
   */
  protected $label;

}
