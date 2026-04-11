<?php
declare(strict_types=1);

namespace TestApp\Auth\AclAdapter;

use TinyAuth\Auth\AclAdapter\AclAdapterInterface;

class FakeAclAdapterA implements AclAdapterInterface {

	/**
	 * @param array $availableRoles
	 * @param array<string, mixed> $config
	 * @return array<string, array<string, mixed>>
	 */
	public function getAcl(array $availableRoles, array $config): array {
		return [
			'Posts' => [
				'plugin' => null,
				'prefix' => null,
				'controller' => 'Posts',
				'allow' => ['edit' => ['user' => 1]],
				'deny' => [],
			],
		];
	}

}
