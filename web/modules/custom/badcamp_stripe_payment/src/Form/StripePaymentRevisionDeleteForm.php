<?php

namespace Drupal\badcamp_stripe_payment\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Stripe payment revision.
 *
 * @ingroup badcamp_stripe_payment
 */
class StripePaymentRevisionDeleteForm extends ConfirmFormBase {


  /**
   * The Stripe payment revision.
   *
   * @var \Drupal\badcamp_stripe_payment\Entity\StripePaymentInterface
   */
  protected $revision;

  /**
   * The Stripe payment storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $StripePaymentStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new StripePaymentRevisionDeleteForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(EntityStorageInterface $entity_storage, Connection $connection) {
    $this->StripePaymentStorage = $entity_storage;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_manager = $container->get('entity.manager');
    return new static(
      $entity_manager->getStorage('stripe_payment'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'stripe_payment_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete the revision from %revision-date?', array('%revision-date' => format_date($this->revision->getRevisionCreationTime())));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.stripe_payment.version_history', array('stripe_payment' => $this->revision->id()));
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $stripe_payment_revision = NULL) {
    $this->revision = $this->StripePaymentStorage->loadRevision($stripe_payment_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->StripePaymentStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Stripe payment: deleted revision %revision.', array('%revision' => $this->revision->getRevisionId()));
    drupal_set_message(t('Revision from %revision-date of Stripe payment has been deleted.', array('%revision-date' => format_date($this->revision->getRevisionCreationTime()))));
    $form_state->setRedirect(
      'entity.stripe_payment.canonical',
       array('stripe_payment' => $this->revision->id())
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {stripe_payment_field_revision} WHERE id = :id', array(':id' => $this->revision->id()))->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.stripe_payment.version_history',
         array('stripe_payment' => $this->revision->id())
      );
    }
  }

}
