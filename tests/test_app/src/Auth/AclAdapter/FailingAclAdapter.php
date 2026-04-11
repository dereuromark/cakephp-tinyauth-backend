<?php
declare(strict_types=1);

namespace TestApp\Auth\AclAdapter;

use RuntimeException;
use TinyAuth\Auth\AclAdapter\AclAdapterInterface;

class FailingAclAdapter implements AclAdapterInterface {

	/**
	 * @param array $availableRoles
	 * @param array<string, mixed> $config
	 * @return array<string, array<string, mixed>>
	 */
	public function getAcl(array $availableRoles, array $config): array {
		throw new RuntimeException('boom');
	}

}
