<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Policy;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use TestApp\Model\Entity\Article;
use TinyAuthBackend\Policy\TinyAuthPolicy;
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;

class TinyAuthPolicyTest extends TestCase {

	use DatabaseTestTrait;

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

}
