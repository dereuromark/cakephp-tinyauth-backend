<?php
declare(strict_types=1);

namespace TestApp\Auth\AllowAdapter;

use TinyAuth\Auth\AllowAdapter\AllowAdapterInterface;

class FakeAllowAdapterA implements AllowAdapterInterface {

	/**
	 * @param array<string, mixed> $config
	 * @return array<string, array<string, mixed>>
	 */
	public function getAllow(array $config): array {
		return [
			'Posts' => [
				'plugin' => null,
				'prefix' => null,
				'controller' => 'Posts',
				'allow' => ['index', 'view'],
				'deny' => [],
			],
		];
	}

}
