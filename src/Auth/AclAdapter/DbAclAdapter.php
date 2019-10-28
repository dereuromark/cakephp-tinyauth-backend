<?php

namespace TinyAuthBackend\Auth\AclAdapter;

use TinyAuth\Auth\AclAdapter\AclAdapterInterface;

class DbAclAdapter implements AclAdapterInterface {

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	public function getAcl(array $availableRoles, array $config) {
		$acl = [];

		return $acl;
	}

}
