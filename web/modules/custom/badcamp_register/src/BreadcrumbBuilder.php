<?php

namespace Drupal\badcamp_register;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

class BreadcrumbBuilder implements BreadcrumbBuilderInterface {
	use StringTranslationTrait;

	/**
	 * BreadcrumbBuilder constructor.
	 */
	public function __construct() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function applies(RouteMatchInterface $route_match) {
		return TRUE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function build(RouteMatchInterface $route_match) {
		$breadcrumb = new Breadcrumb();

		$links = [];

		$hide = [
			'entity.user.canonical',
			'badcamp_register.page_1',
			'badcamp_register.page_2',
			'badcamp_register.page_2_swag',
			'badcamp_register.page_3',
		];

		if (in_array($route_match->getRouteName(), $hide)) {
			return $breadcrumb->setLinks($links);
		}

		if ($route_match->getRouteName() == 'view.schedule.page_2' && \Drupal::currentUser()->isAuthenticated()) {
			$links[] = Link::fromTextAndUrl('Home', Url::fromUserInput('/'));
			$links[] = Link::fromTextAndUrl('Full Schedule', Url::fromUserInput('/schedule'));
			$links[] = Link::fromTextAndUrl('Account', Url::fromRoute('entity.user.canonical', ['user' => $route_match->getRawParameter('user')]));
			$links[] = Link::fromTextAndUrl('User Schedule', Url::fromRouteMatch($route_match));
		}
		elseif ($route_match->getRouteName() == 'view.schedule.page_2') {
			return $breadcrumb->setLinks($links);
		}

		if(count($links) > 0) {
			return $breadcrumb->setLinks($links);
		}
	}
}
