<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Utility;

use Cake\TestSuite\TestCase;
use TinyAuth\Utility\Cache as TinyAuthCache;
use TinyAuthBackend\Utility\CacheInvalidator;

class CacheInvalidatorTest extends TestCase {

	/**
	 * Verifies the invalidator clears entries that TinyAuth's own
	 * cache layer wrote — guarding against the previous bug where
	 * `Cake\Cache\Cache::delete('TinyAuth.allow')` on the `default`
	 * engine never matched the real key.
	 *
	 * @return void
	 */
	public function testClearAllowRemovesEntryWrittenByTinyAuthCache(): void {
		TinyAuthCache::write(TinyAuthCache::KEY_ALLOW, ['some' => 'data']);
		$this->assertNotNull(TinyAuthCache::read(TinyAuthCache::KEY_ALLOW));

		CacheInvalidator::clearAllow();

		$this->assertNull(TinyAuthCache::read(TinyAuthCache::KEY_ALLOW));
	}

	/**
	 * @return void
	 */
	public function testClearAclRemovesEntryWrittenByTinyAuthCache(): void {
		TinyAuthCache::write(TinyAuthCache::KEY_ACL, ['some' => 'data']);
		$this->assertNotNull(TinyAuthCache::read(TinyAuthCache::KEY_ACL));

		CacheInvalidator::clearAcl();

		$this->assertNull(TinyAuthCache::read(TinyAuthCache::KEY_ACL));
	}

	/**
	 * @return void
	 */
	public function testClearAllRemovesBoth(): void {
		TinyAuthCache::write(TinyAuthCache::KEY_ALLOW, ['a' => 1]);
		TinyAuthCache::write(TinyAuthCache::KEY_ACL, ['b' => 2]);

		CacheInvalidator::clearAll();

		$this->assertNull(TinyAuthCache::read(TinyAuthCache::KEY_ALLOW));
		$this->assertNull(TinyAuthCache::read(TinyAuthCache::KEY_ACL));
	}

}
