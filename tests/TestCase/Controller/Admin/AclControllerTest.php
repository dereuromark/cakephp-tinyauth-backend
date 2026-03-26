<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Controller\Admin;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Auth\AclAdapter\DbAclAdapter;
use TinyAuthBackend\Service\RoleSourceService;
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;

class AclControllerTest extends TestCase {

	use DatabaseTestTrait;
	use IntegrationTestTrait;

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
		Configure::write('TinyAuthBackend.roleSource', null);
		(new RoleSourceService())->clearCache();

		$this->insertRow('tinyauth_roles', [
			'id' => 1,
			'name' => 'User',
			'alias' => 'user',
			'parent_id' => 2,
			'sort_order' => 1,
		]);
		$this->insertRow('tinyauth_roles', [
			'id' => 2,
			'name' => 'Admin',
			'alias' => 'admin',
			'parent_id' => null,
			'sort_order' => 2,
		]);
		$this->insertRow('tinyauth_controllers', [
			'id' => 1,
			'plugin' => null,
			'prefix' => 'Admin',
			'name' => 'Articles',
		]);
		$this->insertRow('tinyauth_actions', [
			'id' => 1,
			'controller_id' => 1,
			'name' => 'index',
			'is_public' => false,
		]);
	}

	public function testIndex(): void {
		$this->disableErrorHandlerMiddleware();

		$this->get(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Acl', '?' => ['controller_id' => 1]]);

		$this->assertResponseCode(200);
		$this->assertResponseContains('Articles');
		$this->assertResponseContains('index');
	}

	public function testIndexShowsInheritedPermissionState(): void {
		$this->insertRow('tinyauth_acl_permissions', [
			'id' => 1,
			'action_id' => 1,
			'role_id' => 1,
			'type' => 'allow',
		]);

		$this->get(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Acl', '?' => ['controller_id' => 1]]);

		$this->assertResponseCode(200);
		$this->assertResponseContains('data-state="inherited"');
		$this->assertResponseContains('title="Inherited permission"');
	}

	public function testIndexPrefersExplicitDenyOverInheritedPermissionState(): void {
		$this->insertRow('tinyauth_acl_permissions', [
			'id' => 1,
			'action_id' => 1,
			'role_id' => 1,
			'type' => 'allow',
		]);
		$this->insertRow('tinyauth_acl_permissions', [
			'id' => 2,
			'action_id' => 1,
			'role_id' => 2,
			'type' => 'deny',
		]);

		$this->get(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Acl', '?' => ['controller_id' => 1]]);

		$this->assertResponseCode(200);
		$this->assertResponseContains('data-state="deny"');
		$this->assertResponseContains('title="Denied"');
	}

	public function testToggleCreatesPermission(): void {
		$this->post(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Acl', 'action' => 'toggle'], [
			'action_id' => 1,
			'role_id' => 1,
			'type' => 'allow',
		]);

		$this->assertResponseCode(200);
		$this->assertSame(1, $this->countRows('tinyauth_acl_permissions', ['action_id' => 1, 'role_id' => 1, 'type' => 'allow']));
	}

	public function testToggleUpdatesPermission(): void {
		$this->insertRow('tinyauth_acl_permissions', [
			'id' => 1,
			'action_id' => 1,
			'role_id' => 1,
			'type' => 'allow',
		]);

		$this->post(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Acl', 'action' => 'toggle'], [
			'action_id' => 1,
			'role_id' => 1,
			'type' => 'deny',
		]);

		$this->assertResponseCode(200);
		$this->assertSame(1, $this->countRows('tinyauth_acl_permissions', ['action_id' => 1, 'role_id' => 1, 'type' => 'deny']));
	}

	public function testToggleRemovingExplicitDenyFallsBackToInheritedState(): void {
		$this->insertRow('tinyauth_acl_permissions', [
			'id' => 1,
			'action_id' => 1,
			'role_id' => 1,
			'type' => 'allow',
		]);
		$this->insertRow('tinyauth_acl_permissions', [
			'id' => 2,
			'action_id' => 1,
			'role_id' => 2,
			'type' => 'deny',
		]);

		$this->post(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Acl', 'action' => 'toggle'], [
			'action_id' => 1,
			'role_id' => 2,
			'type' => 'none',
		]);

		$this->assertResponseCode(200);
		$this->assertResponseContains('data-state="inherited"');
		$this->assertResponseContains('title="Inherited permission"');
		$this->assertSame(0, $this->countRows('tinyauth_acl_permissions', ['action_id' => 1, 'role_id' => 2]));
	}

	public function testSearchReturnsMatchingInternalRecords(): void {
		$this->get(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Acl', 'action' => 'search', '?' => ['q' => 'ind']]);

		$this->assertResponseCode(200);
		$this->assertResponseContains('index');
	}

}
