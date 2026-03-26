<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Service;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Service\ImportExportService;
use TinyAuthBackend\Service\RoleSourceService;
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;

class ImportExportServiceTest extends TestCase {

	use DatabaseTestTrait;

	protected array $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthRoles',
		'plugin.TinyAuthBackend.TinyAuthControllers',
		'plugin.TinyAuthBackend.TinyAuthActions',
		'plugin.TinyAuthBackend.TinyAuthAclPermissions',
	];

	public function tearDown(): void {
		(new RoleSourceService())->clearCache();
		Configure::delete('TinyAuthBackend.roleSource');

		parent::tearDown();
	}

	public function testImportIniUsesConfiguredExternalRoleSource(): void {
		Configure::write('TinyAuthBackend.roleSource', ['editor' => 10]);
		(new RoleSourceService())->clearCache();

		$result = (new ImportExportService())->importIni("[Posts]\nindex = editor\n");

		$this->assertSame([], $result['errors']);
		$this->assertSame(1, $this->countRows('tinyauth_acl_permissions', ['role_id' => 10, 'type' => 'allow']));
	}

	public function testExportJsonReturnsConfiguredExternalRolesOnly(): void {
		$this->insertRow('tinyauth_roles', [
			'id' => 99,
			'name' => 'Stale',
			'alias' => 'stale',
			'parent_id' => null,
			'sort_order' => 99,
		]);
		Configure::write('TinyAuthBackend.roleSource', ['editor' => 10]);
		(new RoleSourceService())->clearCache();

		$result = (new ImportExportService())->exportJson();
		$aliases = array_map(static fn ($role) => $role->alias, $result['roles']);

		$this->assertSame(['editor'], $aliases);
		$this->assertSame(1, $this->countRows('tinyauth_roles', ['id' => 10, 'alias' => 'editor']));
		$this->assertSame(0, $this->countRows('tinyauth_roles', ['id' => 99, 'alias' => 'stale']));
	}

	public function testExportCsvUsesConfiguredExternalRolesOnly(): void {
		$this->insertRow('tinyauth_roles', [
			'id' => 99,
			'name' => 'Stale',
			'alias' => 'stale',
			'parent_id' => null,
			'sort_order' => 99,
		]);
		$this->insertRow('tinyauth_controllers', [
			'id' => 1,
			'plugin' => null,
			'prefix' => null,
			'name' => 'Posts',
		]);
		$this->insertRow('tinyauth_actions', [
			'id' => 1,
			'controller_id' => 1,
			'name' => 'index',
			'is_public' => false,
		]);
		$this->insertRow('tinyauth_acl_permissions', [
			'id' => 1,
			'action_id' => 1,
			'role_id' => 99,
			'type' => 'allow',
		]);
		Configure::write('TinyAuthBackend.roleSource', ['editor' => 10]);
		(new RoleSourceService())->clearCache();

		$result = (new ImportExportService())->exportCsv();

		$this->assertStringContainsString('Controller,Action,"editor"', $result);
		$this->assertStringNotContainsString('"stale"', $result);
		$this->assertSame(0, $this->countRows('tinyauth_roles', ['id' => 99, 'alias' => 'stale']));
	}

}
