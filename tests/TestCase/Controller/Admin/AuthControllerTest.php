<?php
namespace TinyAuthBackend\Test\TestCase\Controller\Admin;

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
	public $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthAllowRules',
		'plugin.TinyAuthBackend.TinyAuthAclRules'
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		$this->disableErrorHandlerMiddleware();

		$this->get(['prefix' => 'admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Auth']);

		$this->assertResponseCode(200);
	}

}
