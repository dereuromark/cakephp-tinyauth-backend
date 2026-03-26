<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Service;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Service\RoleSourceService;
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;

class RoleSourceServiceTest extends TestCase {

	use DatabaseTestTrait;

	protected array $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthRoles',
	];

	public function setUp(): void {
		parent::setUp();

		(new RoleSourceService())->clearCache();
	}

	public function tearDown(): void {
		(new RoleSourceService())->clearCache();
		Configure::delete('TinyAuthBackend.roleSource');

		parent::tearDown();
	}

	public function testExternalRoleSourceSyncsShadowRows(): void {
		Configure::write('TinyAuthBackend.roleSource', ['editor' => 10, 'manager' => 20]);
		(new RoleSourceService())->clearCache();

		$roles = (new RoleSourceService())->getRoles();

		$this->assertSame(['editor' => 10, 'manager' => 20], $roles);
		$this->assertSame(1, $this->countRows('tinyauth_roles', ['id' => 10, 'alias' => 'editor']));
		$this->assertSame(1, $this->countRows('tinyauth_roles', ['id' => 20, 'alias' => 'manager']));
	}

	public function testExternalRoleSourceClearsStaleParentHierarchy(): void {
		$this->insertRow('tinyauth_roles', [
			'id' => 10,
			'name' => 'Editor',
			'alias' => 'editor',
			'parent_id' => 99,
			'sort_order' => 1,
		]);
		Configure::write('TinyAuthBackend.roleSource', ['editor' => 10]);
		(new RoleSourceService())->clearCache();

		(new RoleSourceService())->getRoles();

		$role = TableRegistry::getTableLocator()->get('tinyauth_roles')->get(10);

		$this->assertNull($role->parent_id);
	}

	public function testExternalRoleSourcePrunesObsoleteShadowRows(): void {
		$this->insertRow('tinyauth_roles', [
			'id' => 10,
			'name' => 'Editor',
			'alias' => 'editor',
			'parent_id' => null,
			'sort_order' => 1,
		]);
		$this->insertRow('tinyauth_roles', [
			'id' => 20,
			'name' => 'Manager',
			'alias' => 'manager',
			'parent_id' => null,
			'sort_order' => 2,
		]);
		Configure::write('TinyAuthBackend.roleSource', ['editor' => 10]);
		(new RoleSourceService())->clearCache();

		$roles = (new RoleSourceService())->getRoles();

		$this->assertSame(['editor' => 10], $roles);
		$this->assertSame(1, $this->countRows('tinyauth_roles', ['id' => 10, 'alias' => 'editor']));
		$this->assertSame(0, $this->countRows('tinyauth_roles', ['id' => 20]));
	}

	public function testExternalRoleSourcePrunesAllShadowRowsWhenSourceIsEmpty(): void {
		$this->insertRow('tinyauth_roles', [
			'id' => 10,
			'name' => 'Editor',
			'alias' => 'editor',
			'parent_id' => null,
			'sort_order' => 1,
		]);
		Configure::write('TinyAuthBackend.roleSource', []);
		(new RoleSourceService())->clearCache();

		$roles = (new RoleSourceService())->getRoles();

		$this->assertSame([], $roles);
		$this->assertSame(0, $this->countRows('tinyauth_roles', ['id' => 10]));
	}

}
