<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Service;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Service\RoleSourceService;

class RoleSourceServiceTest extends TestCase {

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

	protected function countRows(string $table, array $conditions): int {
		return TableRegistry::getTableLocator()->get($table)->find()->where($conditions)->count();
	}

}
