<?php

namespace TinyAuthBackend\Auth\AllowAdapter;

use TinyAuth\Auth\AllowAdapter\AllowAdapterInterface;

class DbAllowAdapter implements AllowAdapterInterface {

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	public function getAllow(array $config) {
		$auth = [];

		return $auth;
	}

}
