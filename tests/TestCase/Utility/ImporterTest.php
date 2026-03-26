<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Utility;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;
use TinyAuthBackend\Utility\Importer;

class ImporterTest extends TestCase {

	use DatabaseTestTrait;

	protected array $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthRoles',
		'plugin.TinyAuthBackend.TinyAuthControllers',
		'plugin.TinyAuthBackend.TinyAuthActions',
		'plugin.TinyAuthBackend.TinyAuthAclPermissions',
	];

	public function setUp(): void {
		parent::setUp();

		Configure::write('Roles', [
			'admin' => 1,
			'user' => 2,
		]);

		$this->insertRow('tinyauth_roles', [
			'id' => 1,
			'name' => 'Admin',
			'alias' => 'admin',
			'parent_id' => null,
			'sort_order' => 1,
		]);
		$this->insertRow('tinyauth_roles', [
			'id' => 2,
			'name' => 'User',
			'alias' => 'user',
			'parent_id' => null,
			'sort_order' => 2,
		]);
	}

	public function tearDown(): void {
		Configure::delete('Roles');

		parent::tearDown();
	}

	public function testImportAclPersistsPermissionsForAllRoles(): void {
		$file = TMP . 'all-roles-acl.ini';
		file_put_contents($file, "[Posts]\nindex = admin, user\n");

		(new Importer())->importAcl($file);

		$this->assertSame(1, $this->countRows('tinyauth_controllers', ['name' => 'Posts']));
		$this->assertSame(1, $this->countRows('tinyauth_actions', ['name' => 'index']));
		$this->assertSame(1, $this->countRows('tinyauth_acl_permissions', ['role_id' => 1, 'type' => 'allow']));
		$this->assertSame(1, $this->countRows('tinyauth_acl_permissions', ['role_id' => 2, 'type' => 'allow']));
	}

}
