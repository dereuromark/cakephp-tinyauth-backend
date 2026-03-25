<?php
declare(strict_types=1);

namespace TinyAuthBackend\Policy;

use Authorization\IdentityInterface;
use Authorization\Policy\BeforePolicyInterface;
use BadMethodCallException;
use Cake\Datasource\EntityInterface;
use Cake\Utility\Inflector;
use TinyAuthBackend\Service\TinyAuthService;

/**
 * Base policy class for TinyAuth-driven entity permissions.
 *
 * Use this as the default policy for entities that should be controlled
 * via the TinyAuth admin UI. Extend for custom logic.
 *
 * Usage in Application.php:
 *   $resolver->map(Post::class, TinyAuthPolicy::class);
 *   $resolver->map(Comment::class, TinyAuthPolicy::class);
 *
 * Or use OrmResolver with TinyAuthPolicy as default:
 *   $resolver = new OrmResolver(TinyAuthPolicy::class);
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
		if (!$identity) {
			return false;
		}

		$user = $identity->getOriginalData();
		if (!$user instanceof EntityInterface) {
			return false;
		}

		// Get user roles
		$roles = $this->tinyAuth->getUserRoles($user);

		// Super admin bypass (optional - can be configured)
		if (in_array('admin', $roles, true) || in_array('superadmin', $roles, true)) {
			return true;
		}

		return null; // Continue to specific checks
	}

	/**
	 * Generic can check for any ability.
	 *
	 * @param \Cake\Datasource\EntityInterface $user The user entity.
	 * @param string $ability The ability to check (view, edit, delete, etc.).
	 * @param \Cake\Datasource\EntityInterface $entity The entity being accessed.
	 * @return bool Whether access is allowed.
	 */
	public function can(EntityInterface $user, string $ability, EntityInterface $entity): bool {
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
	 * Check if user can view the entity.
	 *
	 * @param \Cake\Datasource\EntityInterface $user The user entity.
	 * @param \Cake\Datasource\EntityInterface $entity The entity being accessed.
	 * @return bool Whether access is allowed.
	 */
	public function canView(EntityInterface $user, EntityInterface $entity): bool {
		return $this->can($user, 'view', $entity);
	}

	/**
	 * Check if user can create entities of this type.
	 * Note: Entity is the prototype/empty entity being created.
	 *
	 * @param \Cake\Datasource\EntityInterface $user The user entity.
	 * @param \Cake\Datasource\EntityInterface $entity The entity being created.
	 * @return bool Whether access is allowed.
	 */
	public function canCreate(EntityInterface $user, EntityInterface $entity): bool {
		return $this->can($user, 'create', $entity);
	}

	/**
	 * Check if user can edit the entity.
	 *
	 * @param \Cake\Datasource\EntityInterface $user The user entity.
	 * @param \Cake\Datasource\EntityInterface $entity The entity being edited.
	 * @return bool Whether access is allowed.
	 */
	public function canEdit(EntityInterface $user, EntityInterface $entity): bool {
		return $this->can($user, 'edit', $entity);
	}

	/**
	 * Check if user can delete the entity.
	 *
	 * @param \Cake\Datasource\EntityInterface $user The user entity.
	 * @param \Cake\Datasource\EntityInterface $entity The entity being deleted.
	 * @return bool Whether access is allowed.
	 */
	public function canDelete(EntityInterface $user, EntityInterface $entity): bool {
		return $this->can($user, 'delete', $entity);
	}

	/**
	 * Magic method handler for custom abilities.
	 *
	 * Allows calling canPublish(), canArchive(), etc. without defining each method.
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
					"Method {$method} requires user and entity arguments.",
				);
			}

			return $this->can($args[0], $ability, $args[1]);
		}

		throw new BadMethodCallException("Unknown method: {$method}");
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
		$className = get_class($entity);
		$parts = explode('\\', $className);
		$entityName = end($parts);

		return Inflector::pluralize($entityName);
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
