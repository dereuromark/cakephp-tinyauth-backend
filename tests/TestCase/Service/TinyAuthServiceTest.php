<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Service;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use TestApp\Model\Entity\Article;
use TinyAuthBackend\Service\TinyAuthService;

class TinyAuthServiceTest extends TestCase {

	protected array $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthRoles',
		'plugin.TinyAuthBackend.TinyAuthResources',
		'plugin.TinyAuthBackend.TinyAuthResourceAbilities',
		'plugin.TinyAuthBackend.TinyAuthResourceAcl',
		'plugin.TinyAuthBackend.TinyAuthScopes',
	];

	public function setUp(): void {
		parent::setUp();

		Configure::write('TinyAuthBackend.roleHierarchy', true);
		Configure::write('TinyAuthBackend.multiRole', false);

		$this->insertRow('tinyauth_roles', [
			'id' => 1,
			'name' => 'Administrator',
			'alias' => 'admin',
			'parent_id' => null,
			'sort_order' => 30,
		]);
		$this->insertRow('tinyauth_roles', [
			'id' => 2,
			'name' => 'Moderator',
			'alias' => 'moderator',
			'parent_id' => 1,
			'sort_order' => 20,
		]);
		$this->insertRow('tinyauth_roles', [
			'id' => 3,
			'name' => 'User',
			'alias' => 'user',
			'parent_id' => 2,
			'sort_order' => 10,
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
		$this->insertRow('tinyauth_scopes', [
			'id' => 1,
			'name' => 'own',
			'description' => 'Own records',
			'entity_field' => 'user_id',
			'user_field' => 'id',
		]);
	}

	public function testInheritedAllowComesFromDescendantRoles(): void {
		$this->insertRow('tinyauth_resource_acl', [
			'id' => 1,
			'resource_ability_id' => 1,
			'role_id' => 3,
			'type' => 'allow',
			'scope_id' => null,
		]);

		$service = new TinyAuthService();

		$result = $service->canAccess(['admin'], 'Article', 'edit', new Article(['user_id' => 9]), new Entity(['id' => 9]));

		$this->assertTrue($result);
	}

	public function testDirectDenyBeatsInheritedAllow(): void {
		$this->insertRow('tinyauth_resource_acl', [
			'id' => 1,
			'resource_ability_id' => 1,
			'role_id' => 3,
			'type' => 'allow',
			'scope_id' => null,
		]);
		$this->insertRow('tinyauth_resource_acl', [
			'id' => 2,
			'resource_ability_id' => 1,
			'role_id' => 1,
			'type' => 'deny',
			'scope_id' => null,
		]);

		$service = new TinyAuthService();

		$result = $service->canAccess(['admin'], 'Article', 'edit', new Article(['user_id' => 9]), new Entity(['id' => 9]));

		$this->assertFalse($result);
	}

	public function testGetScopeConditionUsesInheritedRules(): void {
		$this->insertRow('tinyauth_resource_acl', [
			'id' => 1,
			'resource_ability_id' => 1,
			'role_id' => 3,
			'type' => 'allow',
			'scope_id' => 1,
		]);

		$service = new TinyAuthService();

		$result = $service->getScopeCondition(['admin'], 'Article', 'edit', new Entity(['id' => 42]));

		$this->assertSame(['user_id' => 42], $result);
	}

	public function testCanAccessResourceUsesEntityBasename(): void {
		$this->insertRow('tinyauth_resource_acl', [
			'id' => 1,
			'resource_ability_id' => 1,
			'role_id' => 1,
			'type' => 'allow',
			'scope_id' => null,
		]);

		$service = new TinyAuthService();
		$user = new Entity(['id' => 7, 'role_id' => 1]);

		$result = $service->canAccessResource($user, new Article(['user_id' => 7]), 'edit');

		$this->assertTrue($result);
	}

	protected function insertRow(string $table, array $data): void {
		TableRegistry::getTableLocator()->get($table)->getConnection()->insert($table, $data);
	}

}
