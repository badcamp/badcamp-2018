<?php

namespace Drupal\badcamp_register\Plugin\Menu;

use Drupal\Core\Menu\MenuLinkDefault;
use Drupal\Core\Menu\StaticMenuLinkOverridesInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MyScheduleLink
 *
 * @package Drupal\badcamp_register\Plugin\Menu
 */
class MyScheduleLink extends MenuLinkDefault {

	/**
	 * @var \Drupal\Core\Session\AccountProxyInterface
	 */
	protected $accountProxy;

	/**
	 * MyScheduleLink constructor.
	 *
	 * @param array $configuration
	 * @param $plugin_id
	 * @param $plugin_definition
	 * @param \Drupal\Core\Menu\StaticMenuLinkOverridesInterface $static_override
	 * @param \Drupal\Core\Session\AccountProxyInterface $accountProxy
	 */
	public function __construct(
		array $configuration,
		$plugin_id,
		$plugin_definition,
		StaticMenuLinkOverridesInterface $static_override,
		AccountProxyInterface $accountProxy
	) {
		parent::__construct($configuration, $plugin_id, $plugin_definition, $static_override);
		$this->accountProxy = $accountProxy;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
		return new static(
			$configuration,
			$plugin_id,
			$plugin_definition,
			$container->get('menu_link.static.overrides'),
			$container->get('current_user')
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isEnabled() {
		if ($this->accountProxy->isAnonymous()) {
			return false;
		}
		return parent::isEnabled();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRouteParameters() {
		return [
			'user' => $this->accountProxy->id(),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCacheMaxAge() {
		return 0;
	}
}
