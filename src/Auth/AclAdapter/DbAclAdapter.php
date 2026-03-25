<?php
declare(strict_types=1);

namespace TinyAuthBackend\Auth\AclAdapter;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use TinyAuth\Auth\AclAdapter\AclAdapterInterface;
use TinyAuthBackend\Service\HierarchyService;

class DbAclAdapter implements AclAdapterInterface {

	/**
	 * @param array<string, int> $availableRoles
	 * @param array<string, mixed> $config
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function getAcl(array $availableRoles, array $config): array {
		$permissionsTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.AclPermissions');

		$permissions = $permissionsTable->find()
			->contain(['Actions.TinyauthControllers', 'Roles'])
			->all();

		$acl = [];
		foreach ($permissions as $permission) {
			$action = $permission->action;
			if (!$action || !$action->tinyauth_controller) {
				continue; // Skip malformed permissions
			}
			$controller = $action->tinyauth_controller;
			$key = $this->buildKey($controller->plugin, $controller->prefix, $controller->name);

			if (!isset($acl[$key])) {
				$acl[$key] = [
					'plugin' => $controller->plugin,
					'prefix' => $controller->prefix,
					'controller' => $controller->name,
					'allow' => [],
					'deny' => [],
				];
			}

			if (!$permission->role) {
				continue; // Skip permissions with missing roles
			}

			$type = $permission->type === 'allow' ? 'allow' : 'deny';
			$roleAlias = $permission->role->alias;
			$roleId = $availableRoles[$roleAlias] ?? null;

			if ($roleId !== null) {
				if (!isset($acl[$key][$type][$action->name])) {
					$acl[$key][$type][$action->name] = [];
				}
				$acl[$key][$type][$action->name][$roleAlias] = $roleId;
			}
		}

		// Apply hierarchy if enabled
		if (Configure::read('TinyAuthBackend.roleHierarchy')) {
			$hierarchyService = new HierarchyService();
			$acl = $hierarchyService->applyInheritance($acl, $availableRoles);
		}

		return $acl;
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
