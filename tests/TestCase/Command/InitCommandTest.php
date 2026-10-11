<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Service\RoleSourceService;
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;

class InitCommandTest extends TestCase {

	use ConsoleIntegrationTestTrait;
	use DatabaseTestTrait;

	protected array $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthRoles',
		'plugin.TinyAuthBackend.TinyAuthControllers',
		'plugin.TinyAuthBackend.TinyAuthActions',
		'plugin.TinyAuthBackend.TinyAuthAclPermissions',
	];

	public function setUp(): void {
		parent::setUp();

		$this->loadPlugins(['TinyAuthBackend']);
		Configure::write('Roles', ['admin' => 1]);

		$this->insertRow('tinyauth_roles', [
			'id' => 1,
			'name' => 'Admin',
			'alias' => 'admin',
			'parent_id' => null,
			'sort_order' => 1,
		]);
	}

	public function testInitCommandOutputsDashboardUrlAndCreatesPermissions(): void {
		$this->exec('tiny_auth_backend init admin');

		$this->assertExitSuccess();
		$this->assertOutputContains('/admin/auth');
		$this->assertOutputNotContains('/auth/index');
		$this->assertGreaterThan(0, TableRegistry::getTableLocator()->get('tinyauth_acl_permissions')->find()->count());
	}

	public function testInitUsesBackendRolesWithoutIniRoleConfiguration(): void {
		Configure::delete('Roles');
		Configure::write('TinyAuth.roles', []);
		Configure::write('TinyAuthBackend.roleSource', null);
		(new RoleSourceService())->clearCache();
		$this->exec('tiny_auth_backend init admin');

		$this->assertExitSuccess();
		$this->assertGreaterThan(0, TableRegistry::getTableLocator()->get('TinyAuthBackend.AclPermissions')->find()->where(['role_id' => 1])->count());
	}

}
