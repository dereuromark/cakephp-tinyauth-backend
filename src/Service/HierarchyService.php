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
	 * @var array<string, string> alias => parent_alias
	 */
	protected array $roleParents = [];

	/**
	 * @var array<int, string> id => alias
	 */
	protected array $roleAliasById = [];

	/**
	 * @var bool Whether the hierarchy has been loaded.
	 */
	protected bool $hierarchyLoaded = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Lazy loading - hierarchy loaded on first use
	}

	/**
	 * Ensure role hierarchy is loaded.
	 *
	 * @return void
	 */
	protected function ensureHierarchyLoaded(): void {
		if (!$this->hierarchyLoaded) {
			$this->loadRoleHierarchy();
			$this->hierarchyLoaded = true;
		}
	}

	/**
	 * Load role parent relationships into memory.
	 *
	 * @return void
	 */
	protected function loadRoleHierarchy(): void {
		$rolesTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Roles');
		$roles = $rolesTable->find()->all();

		/** @var \TinyAuthBackend\Model\Entity\Role $role */
		foreach ($roles as $role) {
			$this->roleAliasById[$role->id] = $role->alias;
		}

		// Build parent map using aliases
		/** @var \TinyAuthBackend\Model\Entity\Role $role */
		foreach ($roles as $role) {
			if ($role->parent_id !== null && isset($this->roleAliasById[$role->parent_id])) {
				$this->roleParents[$role->alias] = $this->roleAliasById[$role->parent_id];
			}
		}
	}

	/**
	 * Get all parent roles for a given role alias.
	 *
	 * @param string $roleAlias The role alias to get parents for.
	 * @return array<string> Array of parent role aliases.
	 */
	public function getParentRoles(string $roleAlias): array {
		$this->ensureHierarchyLoaded();

		$parents = [];
		$currentAlias = $roleAlias;

		while (isset($this->roleParents[$currentAlias])) {
			$parentAlias = $this->roleParents[$currentAlias];
			$parents[] = $parentAlias;
			$currentAlias = $parentAlias;
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
		$this->ensureHierarchyLoaded();

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
		$this->ensureHierarchyLoaded();

		$rolesTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Roles');
		/** @var \TinyAuthBackend\Model\Entity\Role|null $parent */
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

		/** @var \TinyAuthBackend\Model\Entity\Role $child */
		foreach ($childRoles as $child) {
			if (isset($availableRoles[$child->alias])) {
				$children[$child->alias] = $availableRoles[$child->alias];
			}
			$this->collectChildren($child->id, $children, $availableRoles, $rolesTable);
		}
	}

}
