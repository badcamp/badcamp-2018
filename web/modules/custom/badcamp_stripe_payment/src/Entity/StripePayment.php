<?php

namespace Drupal\badcamp_stripe_payment\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Stripe payment entity.
 *
 * @ingroup badcamp_stripe_payment
 *
 * @ContentEntityType(
 *   id = "stripe_payment",
 *   label = @Translation("Stripe payment"),
 *   bundle_label = @Translation("Stripe payment type"),
 *   handlers = {
 *     "storage" = "Drupal\badcamp_stripe_payment\StripePaymentStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\badcamp_stripe_payment\StripePaymentListBuilder",
 *     "views_data" = "Drupal\badcamp_stripe_payment\Entity\StripePaymentViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\badcamp_stripe_payment\Form\StripePaymentForm",
 *       "add" = "Drupal\badcamp_stripe_payment\Form\StripePaymentForm",
 *       "edit" = "Drupal\badcamp_stripe_payment\Form\StripePaymentForm",
 *       "delete" = "Drupal\badcamp_stripe_payment\Form\StripePaymentDeleteForm",
 *     },
 *     "access" = "Drupal\badcamp_stripe_payment\StripePaymentAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\badcamp_stripe_payment\StripePaymentHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "stripe_payment",
 *   revision_table = "stripe_payment_revision",
 *   revision_data_table = "stripe_payment_field_revision",
 *   admin_permission = "administer stripe payment entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "stripe_transaction_id" = "stripe_transaction_id",
 *     "stripe_outcome_type" = "stripe_outcome_type",
 *     "paid" = "paid",
 *     "amount" = "amount",
 *     "refunded" = "refunded",
 *     "stripe_status" = "stripe_status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/stripe_payment/{stripe_payment}",
 *     "add-page" = "/admin/structure/stripe_payment/add",
 *     "add-form" = "/admin/structure/stripe_payment/add/{stripe_payment_type}",
 *     "edit-form" = "/admin/structure/stripe_payment/{stripe_payment}/edit",
 *     "delete-form" = "/admin/structure/stripe_payment/{stripe_payment}/delete",
 *     "version-history" = "/admin/structure/stripe_payment/{stripe_payment}/revisions",
 *     "revision" = "/admin/structure/stripe_payment/{stripe_payment}/revisions/{stripe_payment_revision}/view",
 *     "revision_delete" = "/admin/structure/stripe_payment/{stripe_payment}/revisions/{stripe_payment_revision}/delete",
 *     "collection" = "/admin/structure/stripe_payment",
 *   },
 *   bundle_entity_type = "stripe_payment_type",
 *   field_ui_base_route = "entity.stripe_payment_type.edit_form"
 * )
 */
class StripePayment extends RevisionableContentEntityBase implements StripePaymentInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the stripe_payment owner the
    // revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public function getRevisionCreationTime() {
    return $this->get('revision_timestamp')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionCreationTime($timestamp) {
    $this->set('revision_timestamp', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionUser() {
    return $this->get('revision_uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionUserId($uid) {
    $this->set('revision_uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Stripe payment entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['entity_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Related Entity'))
      ->setDescription(t('The related entity this payment belongs to. 
      e.g:Training or donation node / entity.'))
      ->setSetting('target_type', 'node')
      ->setSetting('handler', 'default')
      ->setSetting('default_value',null)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['stripe_transaction_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Stripe Transaction ID'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['stripe_outcome_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Stripe Outcome Type'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['paid'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Paid'))
      ->setDescription(t('Has the charge been paid?'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setSettings([
        'on_label' => t('Yes'),
        'off_label' => t('No'),
        'default_value' => false,
        'display_label' => FALSE,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['amount'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Amount Paid'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'number',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number_integer',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['refunded'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Refunded'))
      ->setDescription(t('Has the charge been refunded?'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setSettings([
        'on_label' => t('Yes'),
        'off_label' => t('No'),
        'default_value' => false,
        'display_label' => FALSE,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['stripe_status'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Stripe Status'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['stripe_customer_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Stripe Customer ID'))
      ->setRequired(FALSE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_timestamp'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Revision timestamp'))
      ->setDescription(t('The time that the current revision was created.'))
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Revision user ID'))
      ->setDescription(t('The user ID of the author of the current revision.'))
      ->setSetting('target_type', 'user')
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    return $fields;
  }

}
