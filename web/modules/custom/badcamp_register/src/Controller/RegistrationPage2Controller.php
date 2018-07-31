<?php

namespace Drupal\badcamp_register\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RegistrationPage2Controller.
 */
class RegistrationPage2Controller extends ControllerBase {

  /** @var \Drupal\Core\Form\FormBuilderInterface $formBuilder */
  protected $formBuilder;

  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
  protected $entityTypeManager;

  /** @var \Drupal\Core\Render\RendererInterface $renderer */
  protected $renderer;

  /** @var \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository */
  protected $entityRepository;

  /**
   * {@inheritdoc}
   */
  public function __construct(FormBuilderInterface $formBuilder, EntityTypeManagerInterface $entityTypeManager, RendererInterface $renderer, EntityRepositoryInterface $entityRepository) {
    $this->formBuilder = $formBuilder;
    $this->entityTypeManager = $entityTypeManager;
    $this->renderer = $renderer;
    $this->entityRepository = $entityRepository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('entity.repository')
    );
  }

  /**
   *
   */
  public function page() {
		if ($this->currentUser()->isAnonymous()){
			return $this->redirect('badcamp_register.page_1');
		}

		$donations = badcamp_stripe_payment_get_donations($this->currentUser->id());
		if(count($donations) > 0) {
			drupal_set_message(t('Thank you for your donation!'));
			return $this->redirect('badcamp_register.page_3');
		}

    $config = $this->config('badcamp_register.settings')->get('page_2');
    $bid = $config['bid']; // Get the block id through config, SQL or some other means
    $block = $this->entityRepository->loadEntityByUuid('block_content', $bid);
    $render = $this->entityTypeManager->getViewBuilder('block_content')->view($block);

    $text = '';
    $format = NULL;
    if(isset($config['intro_message'])) {
      $text = $config['intro_message']['value'];
      $format = $config['intro_message']['format'];
    }
    return [
      '#theme' => 'registration_page',
      '#content' => $render,
      '#step2active' => TRUE,
      '#intro_message' => check_markup($text, $format)
    ];
  }

  /**
   *
   */
  public function swag() {
		if ($this->currentUser()->isAnonymous()){
			return $this->redirect('badcamp_register.page_1');
		}

    $config = $this->config('badcamp_register.settings')->get('page_2_swag');

    $form_class = '\Drupal\badcamp_register\Form\SwagSelectorForm';
    $form = \Drupal::formBuilder()->getForm($form_class);
    $form_rendered = $this->renderer->render($form);

    $text = '';
    $format = NULL;
    if(isset($config['intro_message'])) {
      $text = $config['intro_message']['value'];
      $format = $config['intro_message']['format'];
    }

    return [
      '#theme' => 'registration_page',
      '#content' => $form_rendered,
      '#step2active' => TRUE,
      '#step2active_swag' => TRUE,
      '#intro_message' => [
        '#type' => 'processed_text',
        '#text' => $text,
        '#format' => $format,
      ],
    ];
  }

}
