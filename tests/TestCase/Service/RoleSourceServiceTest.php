<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Service;

use Cake\Core\Configure;
use Cake\Log\Engine\ArrayLog;
use Cake\Log\Log;
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

	public function testExternalRoleSourceDropsNonNumericIdsAndLogs(): void {
		Log::setConfig('uuid-drop-test', ['className' => ArrayLog::class, 'levels' => ['warning']]);
		try {
			Configure::write('TinyAuthBackend.roleSource', [
				'editor' => 10,
				'admin' => '550e8400-e29b-41d4-a716-446655440000',
				'legacy' => 'not-a-number',
				'ok' => '20',
			]);
			(new RoleSourceService())->clearCache();

			$roles = (new RoleSourceService())->getRoles();

			// UUID + non-numeric string dropped, numeric string coerced to int.
			$this->assertSame(['editor' => 10, 'ok' => 20], $roles);

			/** @var \Cake\Log\Engine\ArrayLog $log */
			$log = Log::engine('uuid-drop-test');
			$messages = $log->read();
			$this->assertNotEmpty($messages, 'Expected a warning log entry for dropped ids');
			$joined = implode("\n", $messages);
			$this->assertStringContainsString('dropped 2 role(s)', $joined);
			$this->assertStringContainsString('admin=', $joined);
			$this->assertStringContainsString('legacy=', $joined);
		} finally {
			Log::drop('uuid-drop-test');
		}
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
