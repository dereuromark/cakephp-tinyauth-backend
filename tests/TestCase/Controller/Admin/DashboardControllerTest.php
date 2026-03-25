<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Controller\Admin;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Service\RoleSourceService;
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;

class DashboardControllerTest extends TestCase {

	use DatabaseTestTrait;
	use IntegrationTestTrait;

	protected array $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthRoles',
		'plugin.TinyAuthBackend.TinyAuthControllers',
		'plugin.TinyAuthBackend.TinyAuthActions',
		'plugin.TinyAuthBackend.TinyAuthAclPermissions',
		'plugin.TinyAuthBackend.TinyAuthResources',
		'plugin.TinyAuthBackend.TinyAuthResourceAbilities',
		'plugin.TinyAuthBackend.TinyAuthResourceAcl',
		'plugin.TinyAuthBackend.TinyAuthScopes',
	];

	public function setUp(): void {
		parent::setUp();

		$this->loadPlugins(['TinyAuthBackend']);
		Configure::write('TinyAuthBackend.roleSource', ['editor' => 7, 'admin' => 9]);
		(new RoleSourceService())->clearCache();

		$this->insertRow('tinyauth_controllers', [
			'id' => 1,
			'plugin' => null,
			'prefix' => null,
			'name' => 'Pages',
		]);
		$this->insertRow('tinyauth_actions', [
			'id' => 1,
			'controller_id' => 1,
			'name' => 'display',
			'is_public' => true,
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
			'name' => 'view',
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
		$this->get(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Dashboard']);

		$this->assertResponseCode(200);
		$this->assertResponseContains('Dashboard');
		$this->assertResponseContains('Pages');
	}

	public function testConcepts(): void {
		$this->get(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Dashboard', 'action' => 'concepts']);

		$this->assertResponseCode(200);
		$this->assertResponseContains('Concepts');
	}

}
