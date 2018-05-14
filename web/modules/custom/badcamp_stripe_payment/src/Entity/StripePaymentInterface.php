<?php

namespace Drupal\badcamp_stripe_payment\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Stripe payment entities.
 *
 * @ingroup badcamp_stripe_payment
 */
interface StripePaymentInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Stripe payment type.
   *
   * @return string
   *   The Stripe payment type.
   */
  public function getType();

  /**
   * Gets the Stripe payment creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Stripe payment.
   */
  public function getCreatedTime();

  /**
   * Sets the Stripe payment creation timestamp.
   *
   * @param int $timestamp
   *   The Stripe payment creation timestamp.
   *
   * @return \Drupal\badcamp_stripe_payment\Entity\StripePaymentInterface
   *   The called Stripe payment entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Stripe payment revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Stripe payment revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\badcamp_stripe_payment\Entity\StripePaymentInterface
   *   The called Stripe payment entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Stripe payment revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Stripe payment revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\badcamp_stripe_payment\Entity\StripePaymentInterface
   *   The called Stripe payment entity.
   */
  public function setRevisionUserId($uid);

}
