<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Auth\AllowAdapter;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use TestApp\Auth\AllowAdapter\FailingAllowAdapter;
use TestApp\Auth\AllowAdapter\FakeAllowAdapterA;
use TestApp\Auth\AllowAdapter\FakeAllowAdapterB;
use TestApp\Auth\AllowAdapter\FakeAllowAdapterC;
use TinyAuthBackend\Auth\AllowAdapter\CompositeAllowAdapter;

class CompositeAllowAdapterTest extends TestCase {

	/**
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		Configure::delete('TinyAuth.allowAdapters');
	}

	/**
	 * Two fake adapters returning overlapping rule keys: the
	 * composite must union their allow/deny lists, not overwrite.
	 *
	 * @return void
	 */
	public function testMergeUnionsAllowAndDenyLists(): void {
		Configure::write('TinyAuth.allowAdapters', [
			FakeAllowAdapterA::class,
			FakeAllowAdapterB::class,
		]);

		$result = (new CompositeAllowAdapter())->getAllow([]);

		$this->assertArrayHasKey('Posts', $result);
		$this->assertEqualsCanonicalizing(
			['index', 'view', 'search'],
			$result['Posts']['allow'],
			'allow lists must be unioned',
		);
		$this->assertEqualsCanonicalizing(
			['drop'],
			$result['Posts']['deny'],
			'deny lists must be unioned',
		);
	}

	/**
	 * Keys unique to one adapter are carried through unchanged.
	 *
	 * @return void
	 */
	public function testDisjointKeysAreCombined(): void {
		Configure::write('TinyAuth.allowAdapters', [
			FakeAllowAdapterA::class,
			FakeAllowAdapterC::class,
		]);

		$result = (new CompositeAllowAdapter())->getAllow([]);

		$this->assertArrayHasKey('Posts', $result);
		$this->assertArrayHasKey('Users', $result);
	}

	/**
	 * A throwing adapter must be skipped; the remaining adapters
	 * still contribute. This is the "DB not ready" safety path.
	 *
	 * @return void
	 */
	public function testFailingAdapterIsSkipped(): void {
		Configure::write('TinyAuth.allowAdapters', [
			FakeAllowAdapterA::class,
			FailingAllowAdapter::class,
			FakeAllowAdapterC::class,
		]);

		$result = (new CompositeAllowAdapter())->getAllow([]);

		$this->assertArrayHasKey('Posts', $result);
		$this->assertArrayHasKey('Users', $result);
	}

	/**
	 * Non-class strings and unknown classes are tolerated silently.
	 *
	 * @return void
	 */
	public function testInvalidAdapterClassIsIgnored(): void {
		Configure::write('TinyAuth.allowAdapters', [
			'NoSuchClass',
			FakeAllowAdapterA::class,
		]);

		$result = (new CompositeAllowAdapter())->getAllow([]);

		$this->assertArrayHasKey('Posts', $result);
	}

}
