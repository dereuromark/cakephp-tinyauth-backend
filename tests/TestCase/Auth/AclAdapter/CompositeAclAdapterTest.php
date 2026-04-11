<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Auth\AclAdapter;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use TestApp\Auth\AclAdapter\FailingAclAdapter;
use TestApp\Auth\AclAdapter\FakeAclAdapterA;
use TestApp\Auth\AclAdapter\FakeAclAdapterB;
use TinyAuthBackend\Auth\AclAdapter\CompositeAclAdapter;

class CompositeAclAdapterTest extends TestCase {

	/**
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		Configure::delete('TinyAuth.aclAdapters');
	}

	/**
	 * Overlapping keys across adapters must union their role maps,
	 * not overwrite. This guards the gradual-adoption scenario: two
	 * sources claiming rules for the same controller.
	 *
	 * @return void
	 */
	public function testMergeUnionsRoleMaps(): void {
		Configure::write('TinyAuth.aclAdapters', [
			FakeAclAdapterA::class,
			FakeAclAdapterB::class,
		]);

		$result = (new CompositeAclAdapter())->getAcl(['user' => 1, 'admin' => 2], []);

		$this->assertArrayHasKey('Posts', $result);
		$this->assertSame(
			['user' => 1, 'admin' => 2],
			$result['Posts']['allow']['edit'],
			'role maps for the same action must merge across sources',
		);
	}

	/**
	 * @return void
	 */
	public function testFailingAdapterIsSkipped(): void {
		Configure::write('TinyAuth.aclAdapters', [
			FakeAclAdapterA::class,
			FailingAclAdapter::class,
		]);

		$result = (new CompositeAclAdapter())->getAcl([], []);

		$this->assertArrayHasKey('Posts', $result);
	}

}
