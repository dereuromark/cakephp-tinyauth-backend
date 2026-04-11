<?php
declare(strict_types=1);

namespace TinyAuthBackend\Policy;

use Authorization\IdentityInterface;
use Authorization\Policy\BeforePolicyInterface;
use BadMethodCallException;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Query\SelectQuery;
use TinyAuthBackend\Service\TinyAuthService;

/**
 * Base policy class for TinyAuth-driven entity permissions.
 *
 * Use this as the default policy for entities that should be controlled
 * via the TinyAuth admin UI. Extend for custom logic.
 *
 * Usage in `Application::getAuthorizationService()`:
 *
 *     $resolver = new MapResolver();
 *     $resolver->map(Article::class, TinyAuthPolicy::class);
 *     $resolver->map(ArticlesTable::class, TinyAuthPolicy::class);
 *     // ...
 *     return new AuthorizationService($resolver);
 *
 * Or with `OrmResolver` and `TinyAuthPolicy` as the default class.
 *
 * Controllers then use the idiomatic Authorization calls:
 *
 *     $this->Authorization->authorize($article, 'edit');
 *     $articles = $this->Authorization->applyScope($query, 'index');
 *
 * Both forms dispatch to this policy, which in turn asks
 * {@see \TinyAuthBackend\Service\TinyAuthService} to resolve the rules
 * against the DB-managed tables.
 *
 * ### Method contract
 *
 * All `can*()` and `scope*()` methods accept a nullable
 * `Authorization\IdentityInterface`, matching CakePHP Authorization's
 * calling convention. The identity's original data is expected to be
 * a Cake entity (loaded by `AuthenticationMiddleware` or by a custom
 * middleware in the app). Null identities always deny.
 *
 * ### Custom abilities
 *
 * Custom ability names (`canPublish`, `canArchive`, ...) work
 * out-of-the-box via `__call`. The ability string passed to the
 * underlying service is the lowercase-first suffix, so `canPublish`
 * becomes ability `publish`.
 */
class TinyAuthPolicy implements BeforePolicyInterface {

	/**
	 * @var \TinyAuthBackend\Service\TinyAuthService
	 */
	protected TinyAuthService $tinyAuth;

	/**
	 * Constructor.
	 *
	 * @param \TinyAuthBackend\Service\TinyAuthService|null $tinyAuth The TinyAuth service instance.
	 */
	public function __construct(?TinyAuthService $tinyAuth = null) {
		$this->tinyAuth = $tinyAuth ?? new TinyAuthService();
	}

	/**
	 * Before hook - allows early bypass for super admin roles.
	 *
	 * @param \Authorization\IdentityInterface|null $identity The identity (user).
	 * @param mixed $resource The resource being accessed.
	 * @param string $action The action being performed.
	 * @return bool|null True to allow, false to deny, null to continue to specific checks.
	 */
	public function before(?IdentityInterface $identity, mixed $resource, string $action): ?bool {
		$user = $this->resolveUser($identity);
		if ($user === null) {
			return false;
		}

		$roles = $this->tinyAuth->getUserRoles($user);
		if (array_intersect($roles, $this->getSuperAdminRoles())) {
			return true;
		}

		return null;
	}

	/**
	 * Generic can check for any ability. Kept public so custom
	 * subclasses can delegate to it without reimplementing the
	 * role/resource plumbing.
	 *
	 * @param \Authorization\IdentityInterface|null $identity The identity.
	 * @param string $ability The ability to check (view, edit, delete, etc.).
	 * @param \Cake\Datasource\EntityInterface $entity The entity being accessed.
	 * @return bool Whether access is allowed.
	 */
	public function can(?IdentityInterface $identity, string $ability, EntityInterface $entity): bool {
		$user = $this->resolveUser($identity);
		if ($user === null) {
			return false;
		}

		$resource = $this->getResourceName($entity);
		$roles = $this->tinyAuth->getUserRoles($user);

		return $this->tinyAuth->canAccess(
			$roles,
			$resource,
			$ability,
			$entity,
			$user,
		);
	}

	/**
	 * @param \Authorization\IdentityInterface|null $identity
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @return bool
	 */
	public function canView(?IdentityInterface $identity, EntityInterface $entity): bool {
		return $this->can($identity, 'view', $entity);
	}

	/**
	 * Check if the user can create entities of this type.
	 * Note: Entity is the prototype/empty entity being created.
	 *
	 * @param \Authorization\IdentityInterface|null $identity
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @return bool
	 */
	public function canCreate(?IdentityInterface $identity, EntityInterface $entity): bool {
		return $this->can($identity, 'create', $entity);
	}

	/**
	 * @param \Authorization\IdentityInterface|null $identity
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @return bool
	 */
	public function canEdit(?IdentityInterface $identity, EntityInterface $entity): bool {
		return $this->can($identity, 'edit', $entity);
	}

	/**
	 * @param \Authorization\IdentityInterface|null $identity
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @return bool
	 */
	public function canDelete(?IdentityInterface $identity, EntityInterface $entity): bool {
		return $this->can($identity, 'delete', $entity);
	}

	/**
	 * Magic method handler for custom abilities.
	 *
	 * Allows calling `canPublish()`, `canArchive()`, etc. without
	 * defining each method. The ability name is the lowercase-first
	 * suffix (`canPublish` → `publish`).
	 *
	 * @param string $method The method name being called.
	 * @param array<mixed> $args The method arguments.
	 * @throws \BadMethodCallException When method doesn't start with 'can'.
	 * @return bool Whether access is allowed.
	 */
	public function __call(string $method, array $args): bool {
		if (str_starts_with($method, 'can')) {
			$ability = lcfirst(substr($method, 3)); // canPublish -> publish
			if (count($args) < 2) {
				throw new BadMethodCallException(
					"Method {$method} requires identity and entity arguments.",
				);
			}

			/** @var \Authorization\IdentityInterface|null $identity */
			$identity = $args[0];
			/** @var \Cake\Datasource\EntityInterface $entity */
			$entity = $args[1];

			return $this->can($identity, $ability, $entity);
		}

		throw new BadMethodCallException("Unknown method: {$method}");
	}

	/**
	 * Default scope for list/index queries — applies the effective
	 * `getScopeCondition()` from TinyAuthService to the query. Used
	 * by `$this->Authorization->applyScope($query, 'index')`.
	 *
	 * Tri-state handling mirrors what `TinyAuthService::getScopeCondition()`
	 * returns:
	 *
	 *   - `null` → no access; query is forced empty (`1 = 0`).
	 *   - `[]` → full access; query is returned untouched.
	 *   - `[...]` → scoped access; conditions are applied, with the
	 *                table alias prepended and null values translated
	 *                to `IS NULL`.
	 *
	 * Super-admin roles bypass scoping entirely.
	 *
	 * @param \Authorization\IdentityInterface|null $identity
	 * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query
	 * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface>
	 */
	public function scopeIndex(?IdentityInterface $identity, SelectQuery $query): SelectQuery {
		return $this->applyScopeConditions($identity, $query, 'view');
	}

	/**
	 * Alias of `scopeIndex()` — useful when `applyScope($query, 'view')`
	 * is used instead of the default `index` action name.
	 *
	 * @param \Authorization\IdentityInterface|null $identity
	 * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query
	 * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface>
	 */
	public function scopeView(?IdentityInterface $identity, SelectQuery $query): SelectQuery {
		return $this->applyScopeConditions($identity, $query, 'view');
	}

	/**
	 * Apply `TinyAuthService::getScopeCondition()` to the given query
	 * for the given ability.
	 *
	 * @param \Authorization\IdentityInterface|null $identity
	 * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query
	 * @param string $ability
	 * @return \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface>
	 */
	protected function applyScopeConditions(?IdentityInterface $identity, SelectQuery $query, string $ability): SelectQuery {
		$user = $this->resolveUser($identity);
		if ($user === null) {
			return $query->where(['1 = 0']);
		}

		$roles = $this->tinyAuth->getUserRoles($user);
		if (array_intersect($roles, $this->getSuperAdminRoles())) {
			return $query;
		}

		$resource = $this->resourceForQuery($query);
		$conditions = $this->tinyAuth->getScopeCondition($roles, $resource, $ability, $user);

		if ($conditions === null) {
			return $query->where(['1 = 0']);
		}
		if ($conditions === []) {
			return $query;
		}

		return $query->where($this->qualifyConditions($conditions, $query));
	}

	/**
	 * Resolve the wrapped Cake entity out of an Authorization identity.
	 * Returns null when the identity is missing or carries non-entity
	 * data, which short-circuits every check to a deny.
	 *
	 * @param \Authorization\IdentityInterface|null $identity
	 * @return \Cake\Datasource\EntityInterface|null
	 */
	protected function resolveUser(?IdentityInterface $identity): ?EntityInterface {
		if ($identity === null) {
			return null;
		}
		$data = $identity->getOriginalData();
		if (!$data instanceof EntityInterface) {
			return null;
		}

		return $data;
	}

	/**
	 * Derive the resource class name from a query. Uses the
	 * repository's configured entity class so the lookup matches how
	 * single-entity checks via `can()` resolve their resource.
	 *
	 * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query
	 * @return string
	 */
	protected function resourceForQuery(SelectQuery $query): string {
		return $query->getRepository()->getEntityClass();
	}

	/**
	 * Table-qualify scope conditions so they don't collide with joined
	 * tables, and translate explicit nulls into `field IS` form.
	 *
	 * @param array<string, mixed> $conditions
	 * @param \Cake\ORM\Query\SelectQuery<\Cake\Datasource\EntityInterface> $query
	 * @return array<string, mixed>
	 */
	protected function qualifyConditions(array $conditions, SelectQuery $query): array {
		$alias = $query->getRepository()->getAlias();

		$out = [];
		foreach ($conditions as $field => $value) {
			if ($field === 'OR' || $field === 'AND') {
				$out[$field] = array_map(
					fn ($sub) => is_array($sub) ? $this->qualifyConditions($sub, $query) : $sub,
					(array)$value,
				);

				continue;
			}

			$qualified = str_contains((string)$field, '.') ? (string)$field : $alias . '.' . $field;

			if ($value === null) {
				$out[$qualified . ' IS'] = null;
			} else {
				$out[$qualified] = $value;
			}
		}

		return $out;
	}

	/**
	 * Get the resource name from an entity.
	 *
	 * Override this method to customize resource name resolution.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity The entity.
	 * @return string The resource name.
	 */
	protected function getResourceName(EntityInterface $entity): string {
		return get_class($entity);
	}

	/**
	 * @return array<string>
	 */
	protected function getSuperAdminRoles(): array {
		$superAdminRole = Configure::read('TinyAuthBackend.superAdminRole');
		if ($superAdminRole === null) {
			$superAdminRole = Configure::read('TinyAuth.superAdminRole');
		}

		if (is_string($superAdminRole) && $superAdminRole !== '') {
			return [$superAdminRole];
		}
		if (is_array($superAdminRole)) {
			return array_values(array_filter($superAdminRole, 'is_string'));
		}

		return ['admin', 'superadmin'];
	}

	/**
	 * Get the TinyAuthService instance.
	 *
	 * Useful for extending policies that need direct service access.
	 *
	 * @return \TinyAuthBackend\Service\TinyAuthService The service instance.
	 */
	protected function getTinyAuthService(): TinyAuthService {
		return $this->tinyAuth;
	}

}
