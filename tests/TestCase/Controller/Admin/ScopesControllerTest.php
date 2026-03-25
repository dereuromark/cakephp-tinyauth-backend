<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Controller\Admin;

<<<<<<< HEAD
<<<<<<< HEAD
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;

class ScopesControllerTest extends TestCase {

	use DatabaseTestTrait;
=======
use Cake\ORM\TableRegistry;
=======
>>>>>>> 9fc4af4 (Fix CI across databases and static checks)
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;

class ScopesControllerTest extends TestCase {

<<<<<<< HEAD
>>>>>>> 58f62ec (Add coverage for remaining admin controllers)
=======
	use DatabaseTestTrait;
>>>>>>> 9fc4af4 (Fix CI across databases and static checks)
	use IntegrationTestTrait;

	protected array $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthScopes',
		'plugin.TinyAuthBackend.TinyAuthResourceAcl',
	];

	public function setUp(): void {
		parent::setUp();

		$this->loadPlugins(['TinyAuthBackend']);

		$this->insertRow('tinyauth_scopes', [
			'id' => 1,
			'name' => 'own',
			'description' => 'Own rows',
			'entity_field' => 'user_id',
			'user_field' => 'id',
		]);
	}

	public function testIndex(): void {
		$this->get(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Scopes']);

		$this->assertResponseCode(200);
		$this->assertResponseContains('own');
	}

	public function testAdd(): void {
		$this->post(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Scopes', 'action' => 'add'], [
			'name' => 'team',
			'description' => 'Same team',
			'entity_field' => 'team_id',
			'user_field' => 'team_id',
		]);

		$this->assertResponseCode(302);
		$this->assertSame(1, $this->countRows('tinyauth_scopes', ['name' => 'team']));
	}

	public function testEdit(): void {
		$this->post(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Scopes', 'action' => 'edit', 1], [
			'name' => 'own',
			'description' => 'Updated',
			'entity_field' => 'user_id',
			'user_field' => 'id',
		]);

		$this->assertResponseCode(302);
		$this->assertSame(1, $this->countRows('tinyauth_scopes', ['id' => 1, 'description' => 'Updated']));
	}

	public function testDelete(): void {
		$this->post(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Scopes', 'action' => 'delete', 1]);

		$this->assertResponseCode(302);
		$this->assertSame(0, $this->countRows('tinyauth_scopes', ['id' => 1]));
	}

<<<<<<< HEAD
<<<<<<< HEAD
=======
	protected function insertRow(string $table, array $data): void {
		TableRegistry::getTableLocator()->get($table)->getConnection()->insert($table, $data);
	}

	protected function countRows(string $table, array $conditions): int {
		return TableRegistry::getTableLocator()->get($table)->find()->where($conditions)->count();
	}

>>>>>>> 58f62ec (Add coverage for remaining admin controllers)
=======
>>>>>>> 9fc4af4 (Fix CI across databases and static checks)
}
