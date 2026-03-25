<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Policy;

use ArrayAccess;
use Authorization\IdentityInterface;
use Authorization\Policy\ResultInterface;
use BadMethodCallException;
use Cake\Datasource\EntityInterface;

class TestIdentity implements IdentityInterface {

	public function __construct(protected EntityInterface&ArrayAccess $data) {
	}

	public function can(string $action, mixed $resource): bool {
		return false;
	}

	public function canResult(string $action, mixed $resource): ResultInterface {
		throw new BadMethodCallException('Not implemented for this test double.');
	}

	public function applyScope(string $action, mixed $resource, mixed ...$optionalArgs): mixed {
		return $resource;
	}

	public function getOriginalData(): ArrayAccess|array {
		return $this->data;
	}

	public function offsetExists(mixed $offset): bool {
		return $this->data->offsetExists($offset);
	}

	public function offsetGet(mixed $offset): mixed {
		return $this->data->offsetGet($offset);
	}

	public function offsetSet(mixed $offset, mixed $value): void {
		$this->data->offsetSet($offset, $value);
	}

	public function offsetUnset(mixed $offset): void {
		$this->data->offsetUnset($offset);
	}

}
