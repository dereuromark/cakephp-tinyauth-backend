<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
<<<<<<< HEAD
<<<<<<< HEAD
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;
=======
>>>>>>> af88ee6 (Finish role source support and replace placeholder tests)
=======
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;
>>>>>>> 9fc4af4 (Fix CI across databases and static checks)

class InitCommandTest extends TestCase {

	use ConsoleIntegrationTestTrait;
<<<<<<< HEAD
<<<<<<< HEAD
	use DatabaseTestTrait;
=======
>>>>>>> af88ee6 (Finish role source support and replace placeholder tests)
=======
	use DatabaseTestTrait;
>>>>>>> 9fc4af4 (Fix CI across databases and static checks)

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

<<<<<<< HEAD
<<<<<<< HEAD
=======
	protected function insertRow(string $table, array $data): void {
		TableRegistry::getTableLocator()->get($table)->getConnection()->insert($table, $data);
	}

>>>>>>> af88ee6 (Finish role source support and replace placeholder tests)
=======
>>>>>>> 9fc4af4 (Fix CI across databases and static checks)
}
