<?php
declare(strict_types=1);

namespace TinyAuthBackend\Service;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use TinyAuthBackend\Model\Entity\ResourceAcl;
use TinyAuthBackend\Model\Entity\Scope;

/**
 * Main service for checking resource-level permissions with scope evaluation.
 */
class TinyAuthService {

	/**
	 * @var \TinyAuthBackend\Service\HierarchyService
	 */
	protected HierarchyService $hierarchyService;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->hierarchyService = new HierarchyService();
	}

	/**
	 * Check if role(s) have ability on resource with scope evaluation.
	 *
	 * @param array<string>|string $roles Single role alias or array of role aliases (multi-role).
	 * @param string $resource The resource name (e.g., 'Article', 'Comment').
	 * @param string $ability The ability name (e.g., 'view', 'edit', 'delete').
	 * @param \Cake\Datasource\EntityInterface|null $entity The entity being accessed (for scope evaluation).
	 * @param \Cake\Datasource\EntityInterface|null $user The current user (for scope evaluation).
	 * @return bool Whether access is allowed.
	 */
	public function canAccess(
		string|array $roles,
		string $resource,
		string $ability,
		?EntityInterface $entity = null,
		?EntityInterface $user = null,
	): bool {
		$roles = (array)$roles;

		foreach ($roles as $role) {
			if ($this->checkRoleAccess($role, $resource, $ability, $entity, $user)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Convenience wrapper for entity-level authorization checks.
	 *
	 * @param \Cake\Datasource\EntityInterface $user The current user entity.
	 * @param \Cake\Datasource\EntityInterface $entity The resource entity.
	 * @param string $ability The ability name.
	 * @return bool Whether access is allowed.
	 */
	public function canAccessResource(EntityInterface $user, EntityInterface $entity, string $ability): bool {
		return $this->canAccess(
			$this->getUserRoles($user),
<<<<<<< HEAD
			$this->getResourceIdentifier($entity),
=======
			$this->getResourceName($entity),
>>>>>>> 11f8781 (Fix auth hierarchy semantics and document usage modes)
			$ability,
			$entity,
			$user,
		);
	}

	/**
	 * Convenience wrapper for type-level checks without an entity instance.
	 *
	 * @param \Cake\Datasource\EntityInterface $user The current user entity.
	 * @param string $resource The resource name.
	 * @param string $ability The ability name.
	 * @return bool Whether access is allowed.
	 */
	public function canPerformAbility(EntityInterface $user, string $resource, string $ability): bool {
		return $this->canAccess($this->getUserRoles($user), $resource, $ability, null, $user);
	}

	/**
	 * Check access for a single role.
	 *
	 * @param string $role The role alias.
	 * @param string $resource The resource name.
	 * @param string $ability The ability name.
	 * @param \Cake\Datasource\EntityInterface|null $entity The entity being accessed.
	 * @param \Cake\Datasource\EntityInterface|null $user The current user.
	 * @return bool Whether access is allowed.
	 */
	protected function checkRoleAccess(
		string $role,
		string $resource,
		string $ability,
		?EntityInterface $entity,
		?EntityInterface $user,
	): bool {
		$rule = $this->getEffectiveResourcePermission($role, $resource, $ability);
		if (!$rule || $rule->type === 'deny') {
			return false;
		}

		// No scope = full access
		if ($rule->scope_id === null) {
			return true;
		}

		// Evaluate scope condition
		return $this->evaluateScope($rule->scope, $entity, $user);
	}

	/**
	 * Get the resource permission rule for a role/resource/ability combination.
	 *
	 * @param string $role The role alias.
	 * @param string $resource The resource name.
	 * @param string $ability The ability name.
	 * @return \TinyAuthBackend\Model\Entity\ResourceAcl|null The permission rule or null if not found.
	 */
	public function getResourcePermission(string $role, string $resource, string $ability): ?ResourceAcl {
		$resourceAclTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.ResourceAcl');

		/** @var \TinyAuthBackend\Model\Entity\ResourceAcl|null $result */
		$result = $resourceAclTable->find()
			->contain(['ResourceAbilities.Resources', 'Roles', 'Scopes'])
			->matching('ResourceAbilities.Resources', function ($q) use ($resource) {
				return $q->where([
					'OR' => [
						'Resources.name' => $resource,
						'Resources.entity_class' => $resource,
					],
				]);
			})
			->matching('ResourceAbilities', function ($q) use ($ability) {
				return $q->where(['ResourceAbilities.name' => $ability]);
			})
			->matching('Roles', function ($q) use ($role) {
				return $q->where(['Roles.alias' => $role]);
			})
			->first();

		return $result;
	}

	/**
	 * Get scope condition for query filtering.
	 *
	 * Returns null for no access, empty array for full access, or conditions array for scoped access.
	 *
	 * @param array<string>|string $roles Single role or array of roles.
	 * @param string $resource The resource name.
	 * @param string $ability The ability name.
	 * @param \Cake\Datasource\EntityInterface $user The current user.
	 * @return array<string, mixed>|null Null = no access, empty array = full access, array = conditions.
	 */
	public function getScopeCondition(
		string|array $roles,
		string $resource,
		string $ability,
		EntityInterface $user,
	): ?array {
		$roles = (array)$roles;
		$conditions = [];
		$hasFullAccess = false;

		foreach ($roles as $role) {
			$rule = $this->getEffectiveResourcePermission($role, $resource, $ability);

			if (!$rule || $rule->type === 'deny') {
				continue;
			}

			if ($rule->scope_id === null) {
				$hasFullAccess = true;

				break;
			}

			// Build scope condition
			$scope = $rule->scope;
			if ($scope) {
				$conditions[] = [$scope->entity_field => $user->get($scope->user_field)];
			}
		}

		if ($hasFullAccess) {
			return []; // Empty = no restrictions
		}

		if (!$conditions) {
			return null; // No access
		}

		// Combine with OR for multi-role
		return count($conditions) === 1 ? $conditions[0] : ['OR' => $conditions];
	}

	/**
	 * Resolve the effective permission for a role, taking hierarchy into account.
	 *
	 * Direct rules win. Only missing rules inherit from descendant roles.
	 *
	 * @param string $role The role alias.
	 * @param string $resource The resource name.
	 * @param string $ability The ability name.
	 * @return \TinyAuthBackend\Model\Entity\ResourceAcl|null
	 */
	protected function getEffectiveResourcePermission(string $role, string $resource, string $ability): ?ResourceAcl {
		$rule = $this->getResourcePermission($role, $resource, $ability);
		if ($rule) {
			return $rule;
		}

		if (!Configure::read('TinyAuthBackend.roleHierarchy')) {
			return null;
		}

		$descendantRoles = $this->hierarchyService->getDescendantRoleAliases($role);
		foreach ($descendantRoles as $descendantRole) {
			$descendantRule = $this->getResourcePermission($descendantRole, $resource, $ability);
			if ($descendantRule) {
				return $descendantRule;
			}
		}

		return null;
	}

	/**
	 * Evaluate a scope condition against an entity and user.
	 *
	 * @param \TinyAuthBackend\Model\Entity\Scope|null $scope The scope to evaluate.
	 * @param \Cake\Datasource\EntityInterface|null $entity The entity being accessed.
	 * @param \Cake\Datasource\EntityInterface|null $user The current user.
	 * @return bool Whether the scope condition is satisfied.
	 */
	protected function evaluateScope(?Scope $scope, ?EntityInterface $entity, ?EntityInterface $user): bool {
		if (!$entity || !$user || !$scope) {
			return false;
		}

		$entityValue = $entity->get($scope->entity_field);
		$userValue = $user->get($scope->user_field);

		return $entityValue !== null && $entityValue === $userValue;
	}

	/**
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @return string
	 */
<<<<<<< HEAD
	protected function getResourceIdentifier(EntityInterface $entity): string {
		return get_class($entity);
=======
	protected function getResourceName(EntityInterface $entity): string {
		$className = get_class($entity);
		$parts = explode('\\', $className);

		return end($parts) ?: '';
>>>>>>> 11f8781 (Fix auth hierarchy semantics and document usage modes)
	}

	/**
	 * Get user's roles based on config.
	 *
	 * @param \Cake\Datasource\EntityInterface $user The user entity.
	 * @return array<string> Array of role aliases.
	 */
	public function getUserRoles(EntityInterface $user): array {
		$multiRole = Configure::read('TinyAuthBackend.multiRole');

		if (!$multiRole) {
			$roleColumn = Configure::read('TinyAuthBackend.roleColumn') ?: 'role_id';
			$rolesTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Roles');
			$role = $rolesTable->find()->where(['id' => $user->get($roleColumn)])->first();

			return $role ? [$role->alias] : [];
		}

		// Multi-role: get from configured association/property on the user entity
		$rolesProperty = (string)(Configure::read('TinyAuthBackend.rolesTable') ?: 'roles');
		$roles = $user->get($rolesProperty) ?? $user->get('roles') ?? [];

		return array_map(function ($r): string {
			if (is_object($r)) {
				/** @var \TinyAuthBackend\Model\Entity\Role $r */
				return $r->alias;
			}

			return (string)$r;
		}, $roles);
	}

}
