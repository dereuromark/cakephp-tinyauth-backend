<?php
namespace TinyAuthBackend\Test\TestCase\Controller\Admin;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Auth\AclAdapter\DbAclAdapter;

/**
 * TinyAuthBackend\Controller\TinyAuthAclRulesController Test Case
 *
 * @uses \TinyAuthBackend\Controller\Admin\AclController
 */
class AclControllerTest extends TestCase {

	use IntegrationTestTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthAclRules',
	];

	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		//$this->loadPlugins(['TinyAuthBackend']);

		Configure::write('Roles', [
			'user' => ROLE_USER,
			'moderator' => ROLE_MODERATOR,
			'admin' => ROLE_ADMIN,
		]);

		Configure::write('TinyAuth.aclAdapter', DbAclAdapter::class);
	}

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		$this->disableErrorHandlerMiddleware();

		$this->get(['prefix' => 'admin', 'plugin' => 'TinyAuthBackend', 'controller' => 'Acl']);

		$this->assertResponseCode(200);
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method
	 *
	 * @return void
	 */
	public function testAdd() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method
	 *
	 * @return void
	 */
	public function testEdit() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method
	 *
	 * @return void
	 */
	public function testDelete() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
