<?php

namespace Drupal\badcamp_register\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RegistrationPage1Controller.
 */
class RegistrationPage1Controller extends ControllerBase {

  /** @var \Drupal\Core\Form\FormBuilderInterface $formBuilder */
  protected $formBuilder;

  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
  protected $entityTypeManager;

  /** @var \Drupal\Core\Render\RendererInterface $renderer */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public function __construct(FormBuilderInterface $formBuilder, EntityTypeManagerInterface $entityTypeManager, RendererInterface $renderer) {
    $this->formBuilder = $formBuilder;
    $this->entityTypeManager = $entityTypeManager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  public function page() {
    $config = $this->config('badcamp_register.settings')->get('page_1');

    $entity = $this->entityTypeManager->getStorage('user')->create(array());
    $formObject = $this->entityTypeManager
      ->getFormObject('user', 'register')
      ->setEntity($entity);
    $form = $this->formBuilder->getForm($formObject);
    $form_rendered = $this->renderer->render($form);

    return [
      '#theme' => 'registration_page',
      '#content' => $form_rendered,
      '#step1active' => TRUE
    ];
  }

}
