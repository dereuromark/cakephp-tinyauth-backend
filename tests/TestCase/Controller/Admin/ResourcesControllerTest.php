<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Controller\Admin;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Service\RoleSourceService;
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;

class ResourcesControllerTest extends TestCase {

	use DatabaseTestTrait;
	use IntegrationTestTrait;

	protected array $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthRoles',
		'plugin.TinyAuthBackend.TinyAuthResources',
		'plugin.TinyAuthBackend.TinyAuthResourceAbilities',
		'plugin.TinyAuthBackend.TinyAuthResourceAcl',
		'plugin.TinyAuthBackend.TinyAuthScopes',
	];

	public function setUp(): void {
		parent::setUp();

		$this->loadPlugins(['TinyAuthBackend']);
		Configure::write('TinyAuthBackend.roleSource', null);
		(new RoleSourceService())->clearCache();

		$this->insertRow('tinyauth_roles', [
			'id' => 1,
			'name' => 'User',
			'alias' => 'user',
			'parent_id' => null,
			'sort_order' => 1,
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
			'description' => 'Own rows',
			'entity_field' => 'user_id',
			'user_field' => 'id',
		]);
	}

	public function testIndex(): void {
		$this->get(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Resources', '?' => ['resource_id' => 1]]);

		$this->assertResponseCode(200);
		$this->assertResponseContains('Article');
		$this->assertResponseContains('TestApp\\Model\\Entity\\Article');
		$this->assertResponseContains('edit');
	}

	public function testToggleCreatesScopedPermission(): void {
		$this->post(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Resources', 'action' => 'toggle'], [
			'ability_id' => 1,
			'role_id' => 1,
			'type' => 'allow',
			'scope_id' => 1,
		]);

		$this->assertResponseCode(200);
		$this->assertSame(1, $this->countRows('tinyauth_resource_acl', [
			'resource_ability_id' => 1,
			'role_id' => 1,
			'type' => 'allow',
			'scope_id' => 1,
		]));
	}

	public function testAddAbility(): void {
		$this->post(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Resources', 'action' => 'addAbility'], [
			'resource_id' => 1,
			'name' => 'publish',
		]);

		$this->assertResponseCode(302);
		$this->assertSame(1, $this->countRows('tinyauth_resource_abilities', [
			'resource_id' => 1,
			'name' => 'publish',
		]));
	}

	public function testDeleteAbility(): void {
		$this->post(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Resources', 'action' => 'deleteAbility', 1]);

		$this->assertResponseCode(302);
		$this->assertSame(0, $this->countRows('tinyauth_resource_abilities', ['id' => 1]));
	}

}
