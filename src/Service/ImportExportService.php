<?php
declare(strict_types=1);

namespace TinyAuthBackend\Service;

use Cake\ORM\TableRegistry;

/**
 * Service for importing and exporting TinyAuth permissions.
 *
 * Supports:
 * - JSON (full export/import)
 * - INI (TinyAuth legacy format)
 * - CSV (spreadsheet-friendly)
 */
class ImportExportService {

	/**
	 * Export all permissions to JSON format.
	 *
	 * @return array<string, mixed>
	 */
	public function exportJson(): array {
		$rolesTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Roles');
		$controllersTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.TinyauthControllers');
		$permissionsTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.AclPermissions');
		$resourcesTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Resources');
		$resourceAclTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.ResourceAcl');
		$scopesTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Scopes');

		return [
			'version' => '1.0',
			'exported_at' => date('c'),
			'roles' => $rolesTable->find()->toArray(),
			'controllers' => $controllersTable->find()->contain(['Actions'])->toArray(),
			'acl_permissions' => $permissionsTable->find()->contain(['Actions', 'Roles'])->toArray(),
			'resources' => $resourcesTable->find()->contain(['ResourceAbilities'])->toArray(),
			'resource_acl' => $resourceAclTable->find()->contain(['ResourceAbilities', 'Roles', 'Scopes'])->toArray(),
			'scopes' => $scopesTable->find()->toArray(),
		];
	}

	/**
	 * Export ACL permissions to TinyAuth INI format.
	 *
	 * @return string
	 */
	public function exportIni(): string {
		$controllersTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.TinyauthControllers');
		$controllers = $controllersTable->find()
			->contain(['Actions.AclPermissions.Roles'])
			->all();

		$lines = ['; TinyAuth ACL Export', '; Generated: ' . date('Y-m-d H:i:s'), ''];

		/** @var \TinyAuthBackend\Model\Entity\TinyauthController $controller */
		foreach ($controllers as $controller) {
			$path = ($controller->plugin ? $controller->plugin . '.' : '')
				. ($controller->prefix ? $controller->prefix . '/' : '')
				. $controller->name;

			$lines[] = "[{$path}]";

			foreach ($controller->actions as $action) {
				$roles = [];
				foreach ($action->acl_permissions as $perm) {
					if ($perm->type === 'allow' && $perm->role) {
						$roles[] = $perm->role->alias;
					}
				}
				if ($roles) {
					$lines[] = "{$action->name} = " . implode(', ', $roles);
				}
			}

			$lines[] = '';
		}

		return implode("\n", $lines);
	}

	/**
	 * Export Allow (public actions) to INI format.
	 *
	 * @return string
	 */
	public function exportAllowIni(): string {
		$controllersTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.TinyauthControllers');
		$controllers = $controllersTable->find()
			->contain(['Actions' => fn ($q) => $q->where(['Actions.is_public' => true])])
			->all();

		$lines = ['; TinyAuth Allow Export', '; Generated: ' . date('Y-m-d H:i:s'), ''];

		/** @var \TinyAuthBackend\Model\Entity\TinyauthController $controller */
		foreach ($controllers as $controller) {
			if (!$controller->actions) {
				continue;
			}

			$path = ($controller->plugin ? $controller->plugin . '.' : '')
				. ($controller->prefix ? $controller->prefix . '/' : '')
				. $controller->name;

			$actions = array_map(fn ($a) => $a->name, $controller->actions);
			$lines[] = "{$path} = " . implode(', ', $actions);
		}

		return implode("\n", $lines);
	}

	/**
	 * Export ACL to CSV format.
	 *
	 * @return string
	 */
	public function exportCsv(): string {
		$controllersTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.TinyauthControllers');
		$rolesTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Roles');

		$roles = $rolesTable->find()->orderBy(['sort_order' => 'ASC'])->all()->toArray();
		$controllers = $controllersTable->find()
			->contain(['Actions.AclPermissions'])
			->all();

		// Header row
		/** @var array<\TinyAuthBackend\Model\Entity\Role> $roles */
		$roleNames = array_map(fn ($r) => '"' . str_replace('"', '""', $r->alias) . '"', $roles);
		$lines = ['Controller,Action,' . implode(',', $roleNames)];

		/** @var \TinyAuthBackend\Model\Entity\TinyauthController $controller */
		foreach ($controllers as $controller) {
			$path = ($controller->plugin ? $controller->plugin . '.' : '')
				. ($controller->prefix ? $controller->prefix . '/' : '')
				. $controller->name;

			foreach ($controller->actions as $action) {
				$row = ['"' . $path . '"', $action->name];

				foreach ($roles as $role) {
					$perm = null;
					foreach ($action->acl_permissions as $p) {
						if ($p->role_id === $role->id) {
							$perm = $p;

							break;
						}
					}
					$row[] = $perm ? ($perm->type === 'allow' ? '1' : '-1') : '0';
				}

				$lines[] = implode(',', $row);
			}
		}

		return implode("\n", $lines);
	}

	/**
	 * Import permissions from TinyAuth INI format.
	 *
	 * @param string $content INI file content.
	 * @param string $mode 'merge' or 'replace'. Currently only 'merge' is implemented.
	 * @return array<string, mixed> Result with keys: controllers, actions, permissions, errors.
	 */
	public function importIni(string $content, string $mode = 'merge'): array {
		$controllersTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.TinyauthControllers');
		$actionsTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Actions');
		$permissionsTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.AclPermissions');
		$rolesTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Roles');

		// Build role lookup
		$roleLookup = [];
		/** @var \TinyAuthBackend\Model\Entity\Role $role */
		foreach ($rolesTable->find()->all() as $role) {
			$roleLookup[$role->alias] = $role->id;
		}

		$result = ['controllers' => 0, 'actions' => 0, 'permissions' => 0, 'errors' => []];

		// Parse INI
		$parsed = parse_ini_string($content, true);
		if ($parsed === false) {
			$result['errors'][] = 'Invalid INI format';

			return $result;
		}

		foreach ($parsed as $controllerPath => $actions) {
			// Parse controller path
			$plugin = null;
			$prefix = null;
			$name = $controllerPath;

			if (str_contains($name, '.')) {
				[$plugin, $name] = explode('.', $name, 2);
			}
			if (str_contains($name, '/')) {
				$parts = explode('/', $name);
				$name = array_pop($parts);
				$prefix = implode('/', $parts);
			}

			// Find or create controller
			$controller = $controllersTable->find()
				->where([
					'plugin IS' => $plugin,
					'prefix IS' => $prefix,
					'name' => $name,
				])
				->first();

			if (!$controller) {
				$controller = $controllersTable->newEntity([
					'plugin' => $plugin,
					'prefix' => $prefix,
					'name' => $name,
				]);
				if ($controllersTable->save($controller)) {
					$result['controllers']++;
				} else {
					$result['errors'][] = "Failed to save controller: {$name}";

					continue;
				}
			}

			// Process actions
			foreach ($actions as $actionName => $rolesList) {
				$action = $actionsTable->find()
					->where(['controller_id' => $controller->id, 'name' => $actionName])
					->first();

				if (!$action) {
					$action = $actionsTable->newEntity([
						'controller_id' => $controller->id,
						'name' => $actionName,
						'is_public' => false,
					]);
					if ($actionsTable->save($action)) {
						$result['actions']++;
					} else {
						$result['errors'][] = "Failed to save action: {$actionName}";

						continue;
					}
				}

				// Parse roles
				$roleAliases = array_map('trim', explode(',', $rolesList));
				foreach ($roleAliases as $alias) {
					if (!isset($roleLookup[$alias])) {
						$result['errors'][] = "Unknown role: {$alias}";

						continue;
					}

					$roleId = $roleLookup[$alias];

					$existing = $permissionsTable->find()
						->where(['action_id' => $action->id, 'role_id' => $roleId])
						->first();

					if (!$existing) {
						$permission = $permissionsTable->newEntity([
							'action_id' => $action->id,
							'role_id' => $roleId,
							'type' => 'allow',
						]);
						if ($permissionsTable->save($permission)) {
							$result['permissions']++;
						} else {
							$result['errors'][] = "Failed to save permission for role: {$alias}";
						}
					}
				}
			}
		}

		return $result;
	}

}
