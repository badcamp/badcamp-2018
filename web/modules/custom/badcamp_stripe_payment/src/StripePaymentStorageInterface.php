<?php

namespace Drupal\badcamp_stripe_payment;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface StripePaymentStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Stripe payment revision IDs for a specific Stripe payment.
   *
   * @param \Drupal\badcamp_stripe_payment\Entity\StripePaymentInterface $entity
   *   The Stripe payment entity.
   *
   * @return int[]
   *   Stripe payment revision IDs (in ascending order).
   */
  public function revisionIds(StripePaymentInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Stripe payment author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Stripe payment revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\badcamp_stripe_payment\Entity\StripePaymentInterface $entity
   *   The Stripe payment entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(StripePaymentInterface $entity);

  /**
   * Unsets the language for all Stripe payment with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
