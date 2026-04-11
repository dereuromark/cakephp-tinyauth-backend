<?php
declare(strict_types=1);

namespace TestApp\Auth\AllowAdapter;

use RuntimeException;
use TinyAuth\Auth\AllowAdapter\AllowAdapterInterface;

class FailingAllowAdapter implements AllowAdapterInterface {

	/**
	 * @param array<string, mixed> $config
	 * @return array<string, array<string, mixed>>
	 */
	public function getAllow(array $config): array {
		throw new RuntimeException('boom');
	}

}
