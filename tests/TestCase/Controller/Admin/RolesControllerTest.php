<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Controller\Admin;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * TinyAuthBackend\Controller\Admin\RolesController Test Case
 *
 * @uses \TinyAuthBackend\Controller\Admin\RolesController
 */
class RolesControllerTest extends TestCase {

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
		$this->disableErrorHandlerMiddleware();

		$this->get(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Roles']);

		$this->assertResponseCode(200);
		$this->assertResponseContains('Roles');
	}

}
