<?php

namespace TinyAuthBackend\Test\TestCase\Controller\Admin;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * @uses \TinyAuthBackend\Controller\Admin\AuthController
 */
class AuthControllerTest extends TestCase {

	use IntegrationTestTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	protected array $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthAllowRules',
		'plugin.TinyAuthBackend.TinyAuthAclRules',
	];

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		//$this->loadPlugins(['TinyAuthBackend']);

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
	public function testIndex() {
		$this->disableErrorHandlerMiddleware();

		$this->get(['prefix' => 'Admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Auth']);

		$this->assertResponseCode(200);
	}

}
