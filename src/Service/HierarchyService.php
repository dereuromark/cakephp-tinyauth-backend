<?php
declare(strict_types=1);

namespace TinyAuthBackend\Service;

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
	 * @var array<string, int> alias => id
	 */
	protected array $roleIdByAlias = [];

	/**
	 * Children rows grouped by parent_id, ordered by (sort_order, id).
	 *
	 * Pre-built once during {@see loadRoleHierarchy()} so that the recursive child/
	 * descendant traversals are pure PHP-side hashmap walks instead of one SELECT
	 * per parent. The previous implementation issued ~N queries for an N-deep tree.
	 *
	 * @var array<int, list<array{id: int, alias: string, sort_order: int|null}>>
	 */
	protected array $childrenByParent = [];

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
		$roles = $rolesTable->find()
			->orderByAsc('sort_order')
			->orderByAsc('id')
			->all();

		/** @var \TinyAuthBackend\Model\Entity\Role $role */
		foreach ($roles as $role) {
			$this->roleAliasById[$role->id] = $role->alias;
			$this->roleIdByAlias[$role->alias] = $role->id;
		}

		/** @var \TinyAuthBackend\Model\Entity\Role $role */
		foreach ($roles as $role) {
			if ($role->parent_id !== null && isset($this->roleAliasById[$role->parent_id])) {
				$this->roleParents[$role->alias] = $this->roleAliasById[$role->parent_id];
				$this->childrenByParent[$role->parent_id][] = [
					'id' => $role->id,
					'alias' => $role->alias,
					'sort_order' => $role->sort_order,
				];
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
		$visited = [];

		while (isset($this->roleParents[$currentAlias]) && !isset($visited[$currentAlias])) {
			$visited[$currentAlias] = true;
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
					// Higher roles inherit lower-role permissions.
					$parentRoles = $this->getParentRoles($roleAlias);
					foreach ($parentRoles as $parentAlias) {
						$parentId = $availableRoles[$parentAlias] ?? null;
						if ($parentId === null) {
							continue;
						}
						// Don't override explicit deny
						if (isset($acl[$key]['deny'][$action][$parentAlias])) {
							continue;
						}
						// Add inherited permission
						$acl[$key]['allow'][$action][$parentAlias] = $parentId;
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

		if (!isset($this->roleIdByAlias[$parentAlias])) {
			return [];
		}

		$children = [];
		$this->collectChildren($this->roleIdByAlias[$parentAlias], $children, $availableRoles);

		return $children;
	}

	/**
	 * Get descendant role aliases ordered from nearest child to deepest descendant.
	 *
	 * @param string $parentAlias The parent role alias.
	 * @return array<string>
	 */
	public function getDescendantRoleAliases(string $parentAlias): array {
		$this->ensureHierarchyLoaded();

		if (!isset($this->roleIdByAlias[$parentAlias])) {
			return [];
		}

		$aliases = [];
		$this->collectDescendantAliases($this->roleIdByAlias[$parentAlias], $aliases);

		return $aliases;
	}

	/**
	 * Recursively collect child roles by walking the in-memory hierarchy.
	 *
	 * @param int $parentId The parent role ID.
	 * @param array<string, int> $children Reference to children array to populate.
	 * @param array<string, int> $availableRoles Available roles as alias => id mapping.
	 * @param array<int, bool> $visited
	 * @return void
	 */
	protected function collectChildren(int $parentId, array &$children, array $availableRoles, array &$visited = []): void {
		if (isset($visited[$parentId])) {
			return;
		}
		$visited[$parentId] = true;

		foreach ($this->childrenByParent[$parentId] ?? [] as $child) {
			if (isset($availableRoles[$child['alias']])) {
				$children[$child['alias']] = $availableRoles[$child['alias']];
			}
			$this->collectChildren($child['id'], $children, $availableRoles, $visited);
		}
	}

	/**
	 * @param int $parentId
	 * @param array<string> $aliases
	 * @param array<int, bool> $visited
	 * @return void
	 */
	protected function collectDescendantAliases(int $parentId, array &$aliases, array &$visited = []): void {
		if (isset($visited[$parentId])) {
			return;
		}
		$visited[$parentId] = true;

		foreach ($this->childrenByParent[$parentId] ?? [] as $child) {
			$aliases[] = $child['alias'];
			$this->collectDescendantAliases($child['id'], $aliases, $visited);
		}
	}

}
