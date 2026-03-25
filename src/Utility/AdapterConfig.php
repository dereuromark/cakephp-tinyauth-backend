<?php
declare(strict_types=1);

namespace TinyAuthBackend\Utility;

use Cake\Core\Configure;
use TinyAuthBackend\Auth\AclAdapter\DbAclAdapter;
use TinyAuthBackend\Auth\AllowAdapter\DbAllowAdapter;

class AdapterConfig {

	/**
	 * @return bool
	 */
	public static function isAllowEnabled(): bool {
		$adapter = Configure::read('TinyAuth.allowAdapter');
		if (!$adapter) {
			return false;
		}

		return $adapter === DbAllowAdapter::class;
	}

	/**
	 * @return bool
	 */
	public static function isAclEnabled(): bool {
		$adapter = Configure::read('TinyAuth.aclAdapter');
		if (!$adapter) {
			return false;
		}

		return $adapter === DbAclAdapter::class;
	}

}
