<?php
declare(strict_types=1);

namespace TinyAuthBackend\Utility;

use TinyAuth\Utility\Cache;

/**
 * Centralized invalidation of the TinyAuth runtime caches.
 *
 * The runtime (TinyAuth\Auth\{AllowTrait,AclTrait}) caches the full
 * allow/acl matrix app-wide under
 * `tiny_auth_allow` / `tiny_auth_acl` in the `_cake_model_` cache
 * config. Previously, table hooks called `Cake\Cache\Cache::delete`
 * with keys like `TinyAuth.allow` against the `default` cache engine,
 * which never matched the real keys — so edits in the admin UI did
 * NOT take effect until the next full cache wipe.
 *
 * This helper routes all invalidations through
 * TinyAuth\Utility\Cache so the correct key + engine are used.
 */
class CacheInvalidator {

	/**
	 * Clear both allow and acl caches. Use after any write that could
	 * change either matrix (role hierarchy changes, controller adds/
	 * removes, etc.).
	 *
	 * @return void
	 */
	public static function clearAll(): void {
		static::clearAllow();
		static::clearAcl();
	}

	/**
	 * @return void
	 */
	public static function clearAllow(): void {
		Cache::clear(Cache::KEY_ALLOW);
	}

	/**
	 * @return void
	 */
	public static function clearAcl(): void {
		Cache::clear(Cache::KEY_ACL);
	}

}
