<?php
declare(strict_types=1);

namespace TinyAuthBackend\Service;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

/**
 * Service for managing role hierarchy and permission inheritance.
 */
class HierarchyService {

	/**
	 * @var array<string, int|null>
	 */
	protected array $roleParents = [];

	/**
	 * Constructor - loads role hierarchy on initialization.
	 */
	public function __construct() {
		$this->loadRoleHierarchy();
	}

	/**
	 * Load role parent relationships into memory.
	 *
	 * @return void
	 */
	protected function loadRoleHierarchy(): void {
		$rolesTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Roles');
		$roles = $rolesTable->find()->all();

		foreach ($roles as $role) {
			$this->roleParents[$role->alias] = $role->parent_id;
		}
	}

	/**
	 * Get all parent roles for a given role alias.
	 *
	 * @param string $roleAlias The role alias to get parents for.
	 * @return array<string> Array of parent role aliases.
	 */
	public function getParentRoles(string $roleAlias): array {
		$parents = [];
		$rolesTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Roles');

		$role = $rolesTable->find()->where(['alias' => $roleAlias])->first();
		while ($role && $role->parent_id) {
			$parent = $rolesTable->get($role->parent_id);
			$parents[] = $parent->alias;
			$role = $parent;
		}

		return $parents;
	}

	/**
	 * Apply role hierarchy inheritance to ACL data.
	 *
	 * @param array<string, array<string, mixed>> $acl The ACL data structure.
	 * @param array<string, int> $availableRoles Available roles as alias => id mapping.
	 * @return array<string, array<string, mixed>> The ACL with inherited permissions applied.
	 */
	public function applyInheritance(array $acl, array $availableRoles): array {
		foreach ($acl as $key => $controllerAcl) {
			if (!isset($controllerAcl['allow'])) {
				continue;
			}

			foreach ($controllerAcl['allow'] as $action => $roles) {
				foreach ($roles as $roleAlias => $roleId) {
					// Get all child roles that should inherit this permission
					$childRoles = $this->getChildRoles($roleAlias, $availableRoles);
					foreach ($childRoles as $childAlias => $childId) {
						// Don't override explicit deny
						if (isset($acl[$key]['deny'][$action][$childAlias])) {
							continue;
						}
						// Add inherited permission
						$acl[$key]['allow'][$action][$childAlias] = $childId;
					}
				}
			}
		}

		return $acl;
	}

	/**
	 * Get all child roles that inherit from a parent role.
	 *
	 * @param string $parentAlias The parent role alias.
	 * @param array<string, int> $availableRoles Available roles as alias => id mapping.
	 * @return array<string, int> Child roles as alias => id mapping.
	 */
	public function getChildRoles(string $parentAlias, array $availableRoles): array {
		$rolesTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Roles');
		$parent = $rolesTable->find()->where(['alias' => $parentAlias])->first();

		if (!$parent) {
			return [];
		}

		$children = [];
		$this->collectChildren($parent->id, $children, $availableRoles, $rolesTable);

		return $children;
	}

	/**
	 * Recursively collect child roles.
	 *
	 * @param int $parentId The parent role ID.
	 * @param array<string, int> $children Reference to children array to populate.
	 * @param array<string, int> $availableRoles Available roles as alias => id mapping.
	 * @param \Cake\ORM\Table $rolesTable The roles table instance.
	 * @return void
	 */
	protected function collectChildren(int $parentId, array &$children, array $availableRoles, Table $rolesTable): void {
		$childRoles = $rolesTable->find()->where(['parent_id' => $parentId])->all();

		foreach ($childRoles as $child) {
			if (isset($availableRoles[$child->alias])) {
				$children[$child->alias] = $availableRoles[$child->alias];
			}
			$this->collectChildren($child->id, $children, $availableRoles, $rolesTable);
		}
	}

}
