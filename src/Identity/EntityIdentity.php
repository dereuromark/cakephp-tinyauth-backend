<?php
declare(strict_types=1);

namespace TinyAuthBackend\Identity;

use ArrayAccess;
use Authorization\AuthorizationServiceInterface;
use Authorization\IdentityInterface;
use Authorization\Policy\ResultInterface;
use BadMethodCallException;
use Cake\Datasource\EntityInterface;

/**
 * Lightweight identity wrapper around a Cake ORM entity.
 *
 * Implements `Authorization\IdentityInterface` against a plain Cake
 * entity without requiring `cakephp/authentication`. Useful for apps
 * that resolve their user from a session, a JWT claim, an upstream
 * SSO gateway, or any other custom middleware — pass the resulting
 * entity to this wrapper and drop it on the request under the
 * configured identity attribute.
 *
 * The authorization service is optional. When omitted, the `can*()`
 * and `applyScope()` methods behave as a no-op (deny / pass-through),
 * which is the correct behavior for strategies that do not load
 * `cakephp/authorization` at all (e.g. the AdapterOnly rung). When a
 * service is provided, the identity delegates to it exactly like the
 * framework's own `IdentityDecorator`.
 *
 * Array-style access (`$identity['id']`, `$identity['role_id']`) is
 * forwarded to the underlying entity. `__call()` and `__get()`
 * proxies forward to the entity as well, so template code can treat
 * the identity interchangeably with the entity it wraps.
 */
class EntityIdentity implements IdentityInterface {

	/**
	 * @var \Cake\Datasource\EntityInterface
	 */
	protected EntityInterface $entity;

	/**
	 * @var \Authorization\AuthorizationServiceInterface|null
	 */
	protected ?AuthorizationServiceInterface $service;

	/**
	 * @param \Cake\Datasource\EntityInterface $entity The user entity.
	 * @param \Authorization\AuthorizationServiceInterface|null $service Optional authorization service.
	 */
	public function __construct(EntityInterface $entity, ?AuthorizationServiceInterface $service = null) {
		$this->entity = $entity;
		$this->service = $service;
	}

	/**
	 * Return the entity's primary key value.
	 *
	 * Mirrors `cakephp/authentication`'s `Identity::getIdentifier()` so
	 * adopters coming from that plugin have a familiar API.
	 *
	 * @return array<array-key, mixed>|string|int|null
	 */
	public function getIdentifier(): array|string|int|null {
		/** @var array<array-key, mixed>|string|int|null $value */
		$value = $this->entity->get('id');

		return $value;
	}

	/**
	 * @inheritDoc
	 */
	public function can(string $action, mixed $resource): bool {
		return $this->service?->can($this, $action, $resource) ?? false;
	}

	/**
	 * @inheritDoc
	 */
	public function canResult(string $action, mixed $resource): ResultInterface {
		if ($this->service === null) {
			throw new BadMethodCallException(
				'EntityIdentity::canResult() requires an AuthorizationService. '
				. 'Construct EntityIdentity with a service, or call can() instead.',
			);
		}

		return $this->service->canResult($this, $action, $resource);
	}

	/**
	 * @inheritDoc
	 */
	public function applyScope(string $action, mixed $resource, mixed ...$optionalArgs): mixed {
		if ($this->service === null) {
			return $resource;
		}

		return $this->service->applyScope($this, $action, $resource, ...$optionalArgs);
	}

	/**
	 * @return \ArrayAccess<string, mixed>|array<string, mixed>
	 */
	public function getOriginalData(): ArrayAccess|array {
		return $this->entity;
	}

	/**
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists(mixed $offset): bool {
		return $this->entity->offsetExists($offset);
	}

	/**
	 * @param mixed $offset
	 * @return mixed
	 */
	public function offsetGet(mixed $offset): mixed {
		return $this->entity->offsetGet($offset);
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet(mixed $offset, mixed $value): void {
		$this->entity->offsetSet($offset, $value);
	}

	/**
	 * @param mixed $offset
	 * @return void
	 */
	public function offsetUnset(mixed $offset): void {
		$this->entity->offsetUnset($offset);
	}

	/**
	 * Proxy property reads to the underlying entity.
	 *
	 * @param string $property
	 * @return mixed
	 */
	public function __get(string $property): mixed {
		return $this->entity->get($property);
	}

	/**
	 * Proxy property-existence checks to the underlying entity.
	 *
	 * @param string $property
	 * @return bool
	 */
	public function __isset(string $property): bool {
		return $this->entity->has($property);
	}

}
