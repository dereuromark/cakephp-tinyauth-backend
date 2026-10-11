<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Auth\AclAdapter\DbAclAdapter;
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;

class ImportCommandTest extends TestCase {

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
		Configure::write('TinyAuth.aclAdapter', DbAclAdapter::class);
		Configure::write('Roles', ['admin' => 1]);

		$this->insertRow('tinyauth_roles', [
			'id' => 1,
			'name' => 'Admin',
			'alias' => 'admin',
			'parent_id' => null,
			'sort_order' => 1,
		]);
	}

	public function tearDown(): void {
		Configure::delete('TinyAuth.aclAdapter');
		Configure::delete('Roles');
		Configure::delete('TinyAuth.aclFilePath');

		parent::tearDown();
	}

	public function testImportAclFromValidIniFilePersistsPermissions(): void {
		$file = TMP . 'happy-path-acl.ini';
		file_put_contents($file, "[Articles]\nindex = admin\n");

		try {
			$this->exec('tiny_auth_backend import acl ' . $file);

			$this->assertExitSuccess();
			$this->assertOutputContains('Imported: happy-path-acl.ini');
			$this->assertSame(1, $this->countRows('tinyauth_controllers', ['name' => 'Articles']));
			$this->assertSame(1, $this->countRows('tinyauth_actions', ['name' => 'index']));
			$this->assertSame(1, $this->countRows('tinyauth_acl_permissions', ['role_id' => 1, 'type' => 'allow']));
		} finally {
			@unlink($file);
		}
	}

	public function testImportAclMissingFileExitsWithoutInsertingRows(): void {
		$this->exec('tiny_auth_backend import acl ' . TMP . 'does-not-exist.ini');

		$this->assertExitError();
		$this->assertErrorContains('does-not-exist.ini does not exist or cannot be found, skipping');
		$this->assertSame(0, $this->countRows('tinyauth_controllers', []));
		$this->assertSame(0, $this->countRows('tinyauth_actions', []));
		$this->assertSame(0, $this->countRows('tinyauth_acl_permissions', []));
	}

	public function testImportUsesDefaultAclFileName(): void {
		$file = TMP . 'auth_acl.ini';
		file_put_contents($file, "[Articles]\nindex = admin\n");
		Configure::delete('TinyAuth.aclFile');
		Configure::write('TinyAuth.aclFilePath', rtrim(TMP, DS));

		try {
			$this->exec('tiny_auth_backend import acl');
			$this->assertExitSuccess();
			$this->assertOutputContains('Imported: auth_acl.ini');
			$this->assertSame(1, $this->countRows('tinyauth_acl_permissions', ['role_id' => 1]));
		} finally {
			unlink($file);
		}
	}

	public function testImportRejectsDirectory(): void {
		$this->exec('tiny_auth_backend import acl ' . TMP);
		$this->assertExitError();
		$this->assertSame(0, $this->countRows('tinyauth_acl_permissions', []));
	}

}
