<?php

namespace Drupal\badcamp_stripe_payment;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;

/**
 * Defines a class to build a listing of Stripe payment entities.
 *
 * @ingroup badcamp_stripe_payment
 */
class StripePaymentListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['transaction_id'] = $this->t('Stripe Transaction ID');
    $header['type'] = $this->t('Payment Type');
    $header['refunded'] = $this->t('Refunded');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\badcamp_stripe_payment\Entity\StripePayment */
    $row['transaction_id'] = $entity->get('stripe_transaction_id')->value;
    $row['type'] = $entity->getType();
    $row['refunded'] = $entity->get('refunded')->first()->getValue()['value'] == 1 ? $this->t('Yes') : $this->t('No');
    return $row + parent::buildRow($entity);
  }

}
