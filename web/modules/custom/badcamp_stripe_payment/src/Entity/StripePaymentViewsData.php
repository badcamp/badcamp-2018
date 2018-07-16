<?php

namespace Drupal\badcamp_stripe_payment\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Stripe payment entities.
 */
class StripePaymentViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
