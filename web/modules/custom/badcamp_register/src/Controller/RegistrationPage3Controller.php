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
class RegistrationPage3Controller extends ControllerBase {

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

  /**
   * @return array
   */
  public function page() {
		if ($this->currentUser()->isAnonymous()){
			return $this->redirect('badcamp_register.page_1');
		}

    $config = $this->config('badcamp_register.settings')->get('page_3');

    $text = '';
    $format = NULL;
    if(isset($config['intro_message'])) {
      $text = $config['intro_message']['value'];
      $format = $config['intro_message']['format'];
    }

    return [
      '#theme' => 'registration_page',
      '#content' => [
        '#type' => 'view',
        '#name' => $config['view'],
        '#display_id' => $config['display_id'],
        '#arguments' => $config['arguments'],
      ],
      '#step3active' => TRUE,
      '#intro_message' => check_markup($text, $format)
    ];
  }

}
