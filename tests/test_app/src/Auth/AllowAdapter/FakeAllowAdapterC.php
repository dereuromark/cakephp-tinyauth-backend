<?php
declare(strict_types=1);

namespace TestApp\Auth\AllowAdapter;

use TinyAuth\Auth\AllowAdapter\AllowAdapterInterface;

class FakeAllowAdapterC implements AllowAdapterInterface {

	/**
	 * @param array<string, mixed> $config
	 * @return array<string, array<string, mixed>>
	 */
	public function getAllow(array $config): array {
		return [
			'Users' => [
				'plugin' => null,
				'prefix' => null,
				'controller' => 'Users',
				'allow' => ['register'],
				'deny' => [],
			],
		];
	}

}
