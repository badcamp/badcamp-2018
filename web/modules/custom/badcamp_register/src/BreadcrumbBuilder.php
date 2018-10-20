<?php

namespace Drupal\badcamp_register;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\system\PathBasedBreadcrumbBuilder;

class BreadcrumbBuilder extends PathBasedBreadcrumbBuilder {
	use StringTranslationTrait;

	/**
	 * {@inheritdoc}
	 */
	public function build(RouteMatchInterface $route_match) {
		/** @var Breadcrumb $breadcrumbs */
		$breadcrumbs = parent::build($route_match);

		$hide = [
			'entity.user.canonical',
			'badcamp_register.page_1',
			'badcamp_register.page_2',
			'badcamp_register.page_2_swag',
			'badcamp_register.page_3',
		];

		if (in_array($route_match->getRouteName(), $hide)) {
			$breadcrumbs = new Breadcrumb();
		}

		if ($route_match->getRouteName() == 'view.schedule.page_2' && \Drupal::currentUser()->isAuthenticated()) {
			$breadcrumbs = new Breadcrumb();
			$breadcrumbs->addLink(Link::createFromRoute(t('Home'), '<front>'));
			$breadcrumbs->addLink(Link::createFromRoute(t('Full Schedule'), 'view.schedule.page_1'));
			$breadcrumbs->addLink(Link::createFromRoute(t('Account'), 'entity.user.canonical', ['user' => $route_match->getRawParameter('user')]));
			$breadcrumbs->addLink(Link::createFromRoute(t('User Schedule'), '<none>'));
		}

		return $breadcrumbs;
	}
}
