<?php
declare(strict_types=1);

namespace TinyAuthBackend\Auth\AllowAdapter;

use Cake\ORM\TableRegistry;
use TinyAuth\Auth\AllowAdapter\AllowAdapterInterface;

class DbAllowAdapter implements AllowAdapterInterface {

	/**
	 * @param array<string, mixed> $config
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function getAllow(array $config): array {
		$actionsTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Actions');

		$actions = $actionsTable->find()
			->contain(['TinyauthControllers'])
			->where(['Actions.is_public' => true])
			->all();

		$allow = [];
		foreach ($actions as $action) {
			$controller = $action->tinyauth_controller;
			$key = $this->buildKey($controller->plugin, $controller->prefix, $controller->name);

			if (!isset($allow[$key])) {
				$allow[$key] = [
					'plugin' => $controller->plugin,
					'prefix' => $controller->prefix,
					'controller' => $controller->name,
					'allow' => [],
					'deny' => [],
				];
			}

			$allow[$key]['allow'][] = $action->name;
		}

		return $allow;
	}

	/**
	 * @param string|null $plugin
	 * @param string|null $prefix
	 * @param string $controller
	 *
	 * @return string
	 */
	protected function buildKey(?string $plugin, ?string $prefix, string $controller): string {
		$key = '';
		if ($plugin) {
			$key .= $plugin . '.';
		}
		if ($prefix) {
			$key .= $prefix . '/';
		}
		$key .= $controller;

		return $key;
	}

}
