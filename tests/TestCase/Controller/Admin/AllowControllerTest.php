<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Controller\Admin;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * TinyAuthBackend\Controller\Admin\AllowController Test Case
 *
 * @uses \TinyAuthBackend\Controller\Admin\AllowController
 */
class AllowControllerTest extends TestCase {

	use IntegrationTestTrait;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->loadPlugins(['TinyAuthBackend']);
	}

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex(): void {
		$this->markTestIncomplete('Requires database fixtures for new tables.');
	}

	/**
	 * Test toggle method
	 *
	 * @return void
	 */
	public function testToggle(): void {
		$this->markTestIncomplete('Requires database fixtures for new tables.');
	}

	/**
	 * Test bulkToggle method
	 *
	 * @return void
	 */
	public function testBulkToggle(): void {
		$this->markTestIncomplete('Requires database fixtures for new tables.');
	}

}
