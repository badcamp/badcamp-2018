<?php

namespace Drupal\badcamp_register\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SessionCreation.
 */
class SessionCreation extends ControllerBase {

	/** @var \Drupal\Core\Form\FormBuilderInterface $formBuilder */
	protected $formBuilder;

	/** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
	protected $entityTypeManager;

	/** @var \Drupal\Core\Render\RendererInterface $renderer */
	protected $renderer;

	/** @var \Drupal\Core\Entity\EntityFormBuilderInterface $entityFormBuilder */
	protected $entityFormBuilder;

	/**
	 * {@inheritdoc}
	 */
	public function __construct(FormBuilderInterface $formBuilder, EntityTypeManagerInterface $entityTypeManager, RendererInterface $renderer, EntityFormBuilderInterface $entityFormBuilder) {
		$this->formBuilder = $formBuilder;
		$this->entityTypeManager = $entityTypeManager;
		$this->renderer = $renderer;
		$this->entityFormBuilder = $entityFormBuilder;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function create(ContainerInterface $container) {
		return new static(
			$container->get('form_builder'),
			$container->get('entity_type.manager'),
			$container->get('renderer'),
			$container->get('entity.form_builder')
		);
	}

	public function page() {
		$entity = $this->entityTypeManager->getStorage('node')->create(array(
			'type' => 'session'
		));

		$formObject = $this->entityTypeManager
			->getFormObject('node','user_entry')
			->setEntity($entity);
		$form = $this->formBuilder->getForm($formObject);
		$form_rendered = $this->renderer->render($form);

		return [
			'#markup' => $form_rendered,
		];
	}

}
