<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Controller\Admin;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;

class AllowControllerTest extends TestCase {

	use DatabaseTestTrait;
	use IntegrationTestTrait;

	protected array $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthControllers',
		'plugin.TinyAuthBackend.TinyAuthActions',
	];

	public function setUp(): void {
		parent::setUp();

		$this->loadPlugins(['TinyAuthBackend']);

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
			'is_public' => false,
		]);
		$this->insertRow('tinyauth_actions', [
			'id' => 2,
			'controller_id' => 1,
			'name' => 'view',
			'is_public' => false,
		]);
	}

	public function testIndex(): void {
		$this->get(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Allow']);

		$this->assertResponseCode(200);
		$this->assertResponseContains('Pages');
		$this->assertResponseContains('display');
	}

	public function testToggle(): void {
		$this->post(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Allow', 'action' => 'toggle'], [
			'action_id' => 1,
			'is_public' => 'true',
		]);

		$this->assertResponseCode(200);
		$this->assertSame(1, $this->countRows('tinyauth_actions', ['id' => 1, 'is_public' => true]));
	}

	public function testBulkToggle(): void {
		$this->post(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Allow', 'action' => 'bulkToggle'], [
			'controller_id' => 1,
			'is_public' => 'true',
		]);

		$this->assertResponseCode(302);
		$this->assertSame(2, $this->countRows('tinyauth_actions', ['controller_id' => 1, 'is_public' => true]));
<<<<<<< HEAD
=======
	}

<<<<<<< HEAD
	protected function insertRow(string $table, array $data): void {
		TableRegistry::getTableLocator()->get($table)->getConnection()->insert($table, $data);
	}

	protected function countRows(string $table, array $conditions): int {
		return TableRegistry::getTableLocator()->get($table)->find()->where($conditions)->count();
>>>>>>> af88ee6 (Finish role source support and replace placeholder tests)
	}

=======
>>>>>>> 9fc4af4 (Fix CI across databases and static checks)
}
