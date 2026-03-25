<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Policy;

<<<<<<< HEAD
use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use TestApp\Model\Entity\Article;
use TinyAuthBackend\Policy\TinyAuthPolicy;
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;

class TinyAuthPolicyTest extends TestCase {

	use DatabaseTestTrait;

=======
use ArrayAccess;
use Authorization\IdentityInterface;
use Authorization\Policy\ResultInterface;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use TestApp\Model\Entity\Article;
use TinyAuthBackend\Policy\TinyAuthPolicy;

class TinyAuthPolicyTest extends TestCase {

>>>>>>> 11f8781 (Fix auth hierarchy semantics and document usage modes)
	protected array $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthRoles',
		'plugin.TinyAuthBackend.TinyAuthResources',
		'plugin.TinyAuthBackend.TinyAuthResourceAbilities',
		'plugin.TinyAuthBackend.TinyAuthResourceAcl',
	];

	public function setUp(): void {
		parent::setUp();

		Configure::write('TinyAuthBackend.roleHierarchy', false);
		Configure::write('TinyAuthBackend.multiRole', false);
		Configure::delete('TinyAuthBackend.superAdminRole');
		Configure::delete('TinyAuth.superAdminRole');

		$this->insertRow('tinyauth_roles', [
			'id' => 1,
			'name' => 'Administrator',
			'alias' => 'admin',
			'parent_id' => null,
			'sort_order' => 100,
		]);
		$this->insertRow('tinyauth_roles', [
			'id' => 2,
			'name' => 'Root',
			'alias' => 'root',
			'parent_id' => null,
			'sort_order' => 200,
		]);
		$this->insertRow('tinyauth_resources', [
			'id' => 1,
			'name' => 'Article',
			'entity_class' => 'TestApp\Model\Entity\Article',
			'table_name' => 'articles',
		]);
		$this->insertRow('tinyauth_resource_abilities', [
			'id' => 1,
			'resource_id' => 1,
			'name' => 'edit',
			'description' => null,
		]);
		$this->insertRow('tinyauth_resource_acl', [
			'id' => 1,
			'resource_ability_id' => 1,
			'role_id' => 1,
			'type' => 'allow',
			'scope_id' => null,
		]);
	}

	public function testCanEditUsesSyncedResourceName(): void {
		$policy = new TinyAuthPolicy();
		$user = new Entity(['id' => 99, 'role_id' => 1]);
		$article = new Article(['id' => 5, 'user_id' => 99]);

		$result = $policy->canEdit($user, $article);

		$this->assertTrue($result);
	}

	public function testBeforeUsesConfiguredSuperAdminRole(): void {
		Configure::write('TinyAuth.superAdminRole', 'root');

		$policy = new TinyAuthPolicy();
		$identity = new TestIdentity(new Entity(['id' => 1, 'role_id' => 2]));

		$result = $policy->before($identity, new Article(), 'edit');

		$this->assertTrue($result);
	}

<<<<<<< HEAD
=======
	protected function insertRow(string $table, array $data): void {
		TableRegistry::getTableLocator()->get($table)->getConnection()->insert($table, $data);
	}

}

class TestIdentity implements IdentityInterface {

	public function __construct(protected EntityInterface&ArrayAccess $data) {
	}

	public function can(string $action, mixed $resource): bool {
		return false;
	}

	public function canResult(string $action, mixed $resource): ResultInterface {
		throw new \BadMethodCallException('Not implemented for this test double.');
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

>>>>>>> 11f8781 (Fix auth hierarchy semantics and document usage modes)
}
