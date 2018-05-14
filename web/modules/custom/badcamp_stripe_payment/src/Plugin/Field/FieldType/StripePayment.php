<?php

namespace Drupal\badcamp_stripe_payment\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'stripe_payment' field type.
 *
 * @FieldType(
 *   id = "stripe_payment",
 *   label = @Translation("Stripe Payment"),
 *   category = @Translation("Payment"),
 *   description = @Translation("Accept Payments using Stripe"),
 *   default_widget = "stripe_payment",
 *   default_formatter = "stripe_payment"
 * )
 */
class StripePayment extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    $defaultStorageSettings = [
      'enable' => 0,
      'avs' => 0,
      'amount' => 0,
      'description_value' => '',
      'description_format' => 'basic_html',
      'button_text' => '',
      'max_payments' => 0,
      'max_purchases' => 0,
      'show_button' => 0,
      'link_button' => 0
    ] + parent::defaultStorageSettings();

    return $defaultStorageSettings;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties['enable'] = DataDefinition::create('boolean')
      ->setLabel(t('Enabled'));

    $properties['avs'] = DataDefinition::create('boolean')
      ->setLabel(t('AVS'));

    $properties['amount'] = DataDefinition::create('integer')
      ->setLabel(t('Amount'));

    $properties['payment_type'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Payment_Type'));

    $properties['description_value'] = DataDefinition::create('string')
      ->setLabel(t('Description'));

    $properties['button_label'] = DataDefinition::create('string')
      ->setLabel(t('Button Label'));

    $properties['show_button'] = DataDefinition::create('boolean')
      ->setLabel(t('Show Button'))
      ->setDescription(t('When button is disabled still show the button.'));

    $properties['link_button'] = DataDefinition::create('string')
      ->setLabel(t('Link'))
      ->setDescription(t('When button is disabled and Show button is enabled, where to link button to.'));

    $properties['description_format'] = DataDefinition::create('filter_format')
      ->setLabel(t('Description format'));

    $properties['max_payments'] = DataDefinition::create('integer')
      ->setLabel(t('Max # of Payments'));

    $properties['max_purchases'] = DataDefinition::create('integer')
      ->setLabel(t('Max # of Purchases'));

    $properties['last_refund_date'] = DataDefinition::create('datetime_iso8601')
      ->setLabel(t('Last refund date'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'amount' => [
          'type' => 'int',
          'unsigned' => TRUE,
          'size' => 'big',
        ],
        'max_payments' => [
          'type' => 'int',
          'unsigned' => TRUE,
          'size' => 'big',
        ],
        'max_purchases' => [
          'type' => 'int',
          'unsigned' => TRUE,
          'size' => 'big',
        ],
        'enable' => [
          'type' => 'int',
          'size' => 'tiny',
        ],
        'avs' => [
          'type' => 'int',
          'size' => 'tiny',
        ],
        'description_value' => [
          'type' => 'text',
          'size' => 'big',
        ],
        'description_format' => [
          'type' => 'varchar_ascii',
          'length' => 255,
        ],
        'payment_type' => [
          'type' => 'varchar_ascii',
          'length' => 255,
          'not null' => TRUE,
        ],
        'button_label' => [
          'type' => 'varchar',
          'length' => 255,
        ],
        'show_button' => [
          'type' => 'int',
          'size' => 'tiny',
        ],
        'link_button' => [
          'type' => 'varchar',
          'length' => 255,
        ],
        'last_refund_date' => [
          'type' => 'varchar',
          'length' => 20,
        ]
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $enabled = $this->get('enable')->getValue() == 1;
    $value = $this->get('amount')->getValue();
    return !$enabled && ($value === NULL || $value === 0);
  }

}
