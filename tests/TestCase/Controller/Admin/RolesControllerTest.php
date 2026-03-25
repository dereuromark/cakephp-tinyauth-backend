<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Controller\Admin;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;

/**
 * TinyAuthBackend\Controller\Admin\RolesController Test Case
 *
 * @uses \TinyAuthBackend\Controller\Admin\RolesController
 */
class RolesControllerTest extends TestCase {

	use DatabaseTestTrait;
	use IntegrationTestTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	protected array $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthRoles',
	];

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->loadPlugins(['TinyAuthBackend']);

		Configure::write('Roles', [
			'user' => ROLE_USER,
			'moderator' => ROLE_MODERATOR,
			'admin' => ROLE_ADMIN,
		]);
	}

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex(): void {
		$this->seedRoles();
		$this->disableErrorHandlerMiddleware();

		$this->get(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Roles']);

		$this->assertResponseCode(200);
		$this->assertResponseContains('Roles');
		$this->assertResponseContains('Administrator');
	}

	public function testAddCreatesRole(): void {
		$this->post(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Roles', 'action' => 'add'], [
			'name' => 'Manager',
			'alias' => 'manager',
			'parent_id' => '',
			'sort_order' => 4,
		]);

		$this->assertResponseCode(302);
		$this->assertSame(1, $this->countRows('tinyauth_roles', ['alias' => 'manager']));
	}

	public function testEditRejectsCircularHierarchy(): void {
		$this->seedRoles();
		$this->disableErrorHandlerMiddleware();

		$this->post(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Roles', 'action' => 'edit', 1], [
			'id' => 1,
			'name' => 'Administrator',
			'alias' => 'admin',
			'parent_id' => 3,
			'sort_order' => 1,
		]);

		$this->assertResponseCode(200);
		$this->assertResponseContains('Circular parent reference detected.');
		$this->assertNull($this->getRoleParentId(1));
	}

	public function testReorderRejectsCircularHierarchy(): void {
		$this->seedRoles();

		$this->post(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Roles', 'action' => 'reorder'], [
			'role_id' => 1,
			'parent_id' => 3,
			'sort_order' => 0,
		]);

		$this->assertResponseCode(200);
		$this->assertResponseContains('"success":false');
		$this->assertResponseContains('Circular parent reference detected.');
		$this->assertNull($this->getRoleParentId(1));
	}

	public function testReorderPersistsValidHierarchyUpdate(): void {
		$this->seedRoles();
		$this->insertRow('tinyauth_roles', [
			'id' => 4,
			'name' => 'Guest',
			'alias' => 'guest',
			'parent_id' => null,
			'sort_order' => 4,
		]);

		$this->post(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Roles', 'action' => 'reorder'], [
			'role_id' => 4,
			'parent_id' => 2,
			'sort_order' => 0,
		]);

		$this->assertResponseCode(200);
		$this->assertResponseContains('"success":true');
		$this->assertSame(2, $this->getRoleParentId(4));
	}

	protected function seedRoles(): void {
		$this->insertRow('tinyauth_roles', [
			'id' => 1,
			'name' => 'Administrator',
			'alias' => 'admin',
			'parent_id' => null,
			'sort_order' => 1,
		]);
		$this->insertRow('tinyauth_roles', [
			'id' => 2,
			'name' => 'Moderator',
			'alias' => 'moderator',
			'parent_id' => 1,
			'sort_order' => 2,
		]);
		$this->insertRow('tinyauth_roles', [
			'id' => 3,
			'name' => 'User',
			'alias' => 'user',
			'parent_id' => 2,
			'sort_order' => 3,
		]);
	}

	protected function getRoleParentId(int $id): ?int {
		/** @var \TinyAuthBackend\Model\Entity\Role $role */
		$role = TableRegistry::getTableLocator()->get('tinyauth_roles')->get($id);

		return $role->parent_id;
	}

}
