<?php

namespace Drupal\badcamp_stripe_payment;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\badcamp_stripe_payment\Entity\StripePaymentInterface;

/**
 * Defines the storage handler class for Stripe payment entities.
 *
 * This extends the base storage class, adding required special handling for
 * Stripe payment entities.
 *
 * @ingroup badcamp_stripe_payment
 */
class StripePaymentStorage extends SqlContentEntityStorage implements StripePaymentStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(StripePaymentInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {stripe_payment_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {stripe_payment_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(StripePaymentInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {stripe_payment_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('stripe_payment_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
