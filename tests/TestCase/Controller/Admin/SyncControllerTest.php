<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Controller\Admin;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;

class SyncControllerTest extends TestCase {

	use DatabaseTestTrait;
	use IntegrationTestTrait;

	protected array $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthControllers',
		'plugin.TinyAuthBackend.TinyAuthActions',
		'plugin.TinyAuthBackend.TinyAuthResources',
		'plugin.TinyAuthBackend.TinyAuthResourceAbilities',
	];

	public function setUp(): void {
		parent::setUp();

		$this->loadPlugins(['TinyAuthBackend']);
	}

	public function testControllersIndex(): void {
		$this->get(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Sync', 'action' => 'controllers']);

		$this->assertResponseCode(200);
		$this->assertResponseContains('Sync');
	}

	public function testControllersSync(): void {
		$this->post(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Sync', 'action' => 'controllers'], [
			'add_new' => '1',
			'add_actions' => '1',
		]);

		$this->assertResponseCode(302);
		$this->assertGreaterThan(0, TableRegistry::getTableLocator()->get('tinyauth_controllers')->find()->count());
		$this->assertGreaterThan(0, TableRegistry::getTableLocator()->get('tinyauth_actions')->find()->count());
	}

	public function testResourcesIndex(): void {
		$this->get(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Sync', 'action' => 'resources']);

		$this->assertResponseCode(200);
		$this->assertResponseContains('Sync');
	}

	public function testResourcesIndexMatchesExistingRowsByEntityClass(): void {
		$this->insertRow('tinyauth_resources', [
			'id' => 1,
			'name' => 'Role',
			'entity_class' => 'Other\\Plugin\\Model\\Entity\\Role',
			'table_name' => 'other_roles',
		]);

		$this->get(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Sync', 'action' => 'resources']);

		$this->assertResponseCode(200);
		$this->assertResponseContains('TinyAuthBackend\\Model\\Entity\\Role');
		$this->assertResponseContains('+ NEW');
	}

	public function testResourcesSync(): void {
		$this->post(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Sync', 'action' => 'resources'], [
			'add_new' => '1',
			'add_abilities' => '1',
		]);

		$this->assertResponseCode(302);
		$this->assertGreaterThan(0, TableRegistry::getTableLocator()->get('tinyauth_resources')->find()->count());
		$this->assertGreaterThan(0, TableRegistry::getTableLocator()->get('tinyauth_resource_abilities')->find()->count());
	}

}
