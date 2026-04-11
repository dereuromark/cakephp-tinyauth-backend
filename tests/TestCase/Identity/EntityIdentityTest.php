<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Identity;

use Authorization\AuthorizationServiceInterface;
use Authorization\Policy\Result;
use BadMethodCallException;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Identity\EntityIdentity;

class EntityIdentityTest extends TestCase {

	public function testGetIdentifierReturnsEntityId(): void {
		$entity = new Entity(['id' => 42, 'name' => 'Alice']);
		$identity = new EntityIdentity($entity);

		$this->assertSame(42, $identity->getIdentifier());
	}

	public function testGetIdentifierReturnsNullWhenIdMissing(): void {
		$entity = new Entity(['name' => 'Alice']);
		$identity = new EntityIdentity($entity);

		$this->assertNull($identity->getIdentifier());
	}

	public function testGetOriginalDataReturnsEntity(): void {
		$entity = new Entity(['id' => 1, 'name' => 'Alice']);
		$identity = new EntityIdentity($entity);

		$this->assertSame($entity, $identity->getOriginalData());
	}

	public function testArrayAccessForwardsToEntity(): void {
		$entity = new Entity(['id' => 1, 'role_id' => 5]);
		$identity = new EntityIdentity($entity);

		$this->assertTrue(isset($identity['id']));
		$this->assertSame(5, $identity['role_id']);
		$this->assertFalse(isset($identity['missing']));
	}

	public function testArrayAccessWritesAreForwardedToEntity(): void {
		$entity = new Entity(['id' => 1]);
		$identity = new EntityIdentity($entity);

		$identity['role_id'] = 7;
		$this->assertSame(7, $entity->get('role_id'));

		unset($identity['role_id']);
		$this->assertFalse($entity->has('role_id'));
	}

	public function testMagicPropertyAccessForwardsToEntity(): void {
		$entity = new Entity(['id' => 1, 'name' => 'Alice']);
		$identity = new EntityIdentity($entity);

		$this->assertSame('Alice', $identity->name);
		$this->assertTrue(isset($identity->name));
		$this->assertFalse(isset($identity->missing));
	}

	public function testCanWithoutServiceReturnsFalse(): void {
		$entity = new Entity(['id' => 1]);
		$identity = new EntityIdentity($entity);

		$this->assertFalse($identity->can('edit', new Entity()));
	}

	public function testApplyScopeWithoutServiceReturnsResourceUnchanged(): void {
		$entity = new Entity(['id' => 1]);
		$identity = new EntityIdentity($entity);

		$resource = new Entity(['title' => 'hello']);
		$this->assertSame($resource, $identity->applyScope('index', $resource));
	}

	public function testCanResultWithoutServiceThrows(): void {
		$entity = new Entity(['id' => 1]);
		$identity = new EntityIdentity($entity);

		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('requires an AuthorizationService');

		$identity->canResult('edit', new Entity());
	}

	public function testCanDelegatesToService(): void {
		$entity = new Entity(['id' => 1]);
		$resource = new Entity(['id' => 9]);

		$service = $this->createMock(AuthorizationServiceInterface::class);
		$service->expects($this->once())
			->method('can')
			->with($this->isInstanceOf(EntityIdentity::class), 'edit', $resource)
			->willReturn(true);

		$identity = new EntityIdentity($entity, $service);
		$this->assertTrue($identity->can('edit', $resource));
	}

	public function testCanResultDelegatesToService(): void {
		$entity = new Entity(['id' => 1]);
		$resource = new Entity(['id' => 9]);
		$result = new Result(true);

		$service = $this->createMock(AuthorizationServiceInterface::class);
		$service->expects($this->once())
			->method('canResult')
			->with($this->isInstanceOf(EntityIdentity::class), 'edit', $resource)
			->willReturn($result);

		$identity = new EntityIdentity($entity, $service);
		$this->assertSame($result, $identity->canResult('edit', $resource));
	}

	public function testApplyScopeDelegatesToService(): void {
		$entity = new Entity(['id' => 1]);
		$resource = new Entity(['scoped' => false]);
		$scoped = new Entity(['scoped' => true]);

		$service = $this->createMock(AuthorizationServiceInterface::class);
		$service->expects($this->once())
			->method('applyScope')
			->with($this->isInstanceOf(EntityIdentity::class), 'index', $resource)
			->willReturn($scoped);

		$identity = new EntityIdentity($entity, $service);
		$this->assertSame($scoped, $identity->applyScope('index', $resource));
	}

}
