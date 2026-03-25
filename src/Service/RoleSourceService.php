<?php
declare(strict_types=1);

namespace TinyAuthBackend\Service;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * Service for fetching roles from configurable sources.
 *
 * Supports:
 * - null: Use tinyauth_roles table (default)
 * - string: Configure path to read from
 * - array: Direct role mapping [alias => id]
 * - callable: Function returning roles
 */
class RoleSourceService {

	/**
	 * Cached roles.
	 *
	 * @var array<string, int>|null
	 */
	protected static ?array $cachedRoles = null;

	/**
	 * Get all roles as alias => id mapping.
	 *
	 * @return array<string, int> Role alias => id mapping.
	 */
	public function getRoles(): array {
		if (static::$cachedRoles !== null) {
			return static::$cachedRoles;
		}

		$roleSource = Configure::read('TinyAuthBackend.roleSource');

		if ($roleSource === null) {
			// Default: use tinyauth_roles table
			static::$cachedRoles = $this->getRolesFromTable();
		} elseif (is_string($roleSource)) {
			// Configure path
			$roles = Configure::read($roleSource);
			static::$cachedRoles = $this->normalizeRoles(is_array($roles) ? $roles : []);
		} elseif (is_array($roleSource)) {
			// Direct array
			static::$cachedRoles = $this->normalizeRoles($roleSource);
		} elseif (is_callable($roleSource)) {
			// Callable
			$roles = $roleSource();
			static::$cachedRoles = $this->normalizeRoles(is_array($roles) ? $roles : []);
		} else {
			static::$cachedRoles = [];
		}

		if ($roleSource !== null && static::$cachedRoles) {
			$this->syncExternalRoles(static::$cachedRoles);
		}

		return static::$cachedRoles ?? [];
	}

	/**
	 * Get roles as entity objects (for UI display with hierarchy).
	 *
	 * Only available when using tinyauth_roles table. Returns simple objects for external sources.
	 *
	 * @return array<object>
	 */
	public function getRoleEntities(): array {
		$roleSource = Configure::read('TinyAuthBackend.roleSource');

		if ($roleSource !== null) {
			// External source - convert to simple objects
			$roles = $this->getRoles();
			$result = [];

			foreach ($roles as $alias => $id) {
				$result[] = (object)[
					'id' => $id,
					'alias' => $alias,
					'name' => ucfirst((string)$alias),
					'parent_id' => null,
					'parent' => null,
					'sort_order' => 0,
				];
			}

			return $result;
		}

		// Default: fetch from table with hierarchy
		/** @var \TinyAuthBackend\Model\Table\RolesTable $rolesTable */
		$rolesTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Roles');

		/** @var array<\TinyAuthBackend\Model\Entity\Role> $entities */
		$entities = $rolesTable->find()
			->contain(['ParentRoles'])
			->orderBy(['Roles.sort_order' => 'ASC', 'Roles.name' => 'ASC'])
			->all()
			->toArray();

		return $entities;
	}

	/**
	 * Check if roles are managed by this plugin (vs external source).
	 *
	 * @return bool True if roles are managed by this plugin.
	 */
	public function isManaged(): bool {
		return Configure::read('TinyAuthBackend.roleSource') === null;
	}

	/**
	 * Clear cached roles.
	 *
	 * @return void
	 */
	public function clearCache(): void {
		static::$cachedRoles = null;
	}

	/**
	 * Get roles from the tinyauth_roles table.
	 *
	 * @return array<string, int> Role alias => id mapping.
	 */
	protected function getRolesFromTable(): array {
		try {
			$rolesTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Roles');

			/** @var array<string, int> $roles */
			$roles = $rolesTable->find('list', keyField: 'alias', valueField: 'id')
				->toArray();

			return $roles;
		} catch (Exception $e) {
			return [];
		}
	}

	/**
	 * @param array<mixed, mixed> $roles
	 * @return array<string, int>
	 */
	protected function normalizeRoles(array $roles): array {
		$result = [];
		foreach ($roles as $alias => $id) {
			if (!is_string($alias) || $alias === '') {
				continue;
			}
			if (!is_numeric($id)) {
				continue;
			}

			$result[$alias] = (int)$id;
		}

		return $result;
	}

	/**
	 * Create/update shadow role rows so FK-backed permission tables remain usable.
	 *
	 * @param array<string, int> $roles
	 * @return void
	 */
	protected function syncExternalRoles(array $roles): void {
		try {
			/** @var \TinyAuthBackend\Model\Table\RolesTable $rolesTable */
			$rolesTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Roles');
		} catch (Exception $e) {
			return;
		}

		$sortOrder = 0;
		foreach ($roles as $alias => $id) {
			$sortOrder++;

			$role = $rolesTable->find()->where(['id' => $id])->first();
			if (!$role) {
				$role = $rolesTable->find()->where(['alias' => $alias])->first();
			}

			if ($role) {
				$role = $rolesTable->patchEntity($role, [
					'id' => $id,
					'alias' => $alias,
					'name' => $role->name ?: ucfirst($alias),
					'sort_order' => $role->sort_order ?: $sortOrder,
				]);
				$rolesTable->save($role);

				continue;
			}

			$role = $rolesTable->newEntity([
				'id' => $id,
				'alias' => $alias,
				'name' => ucfirst($alias),
				'parent_id' => null,
				'sort_order' => $sortOrder,
			], ['accessibleFields' => ['id' => true]]);
			$rolesTable->save($role);
		}
	}

}
