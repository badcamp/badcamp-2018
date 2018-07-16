<?php

namespace Drupal\badcamp_stripe_payment;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Stripe payment entity.
 *
 * @see \Drupal\badcamp_stripe_payment\Entity\StripePayment.
 */
class StripePaymentAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\badcamp_stripe_payment\Entity\StripePaymentInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view published stripe payment entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit stripe payment entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete stripe payment entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add stripe payment entities');
  }

}
