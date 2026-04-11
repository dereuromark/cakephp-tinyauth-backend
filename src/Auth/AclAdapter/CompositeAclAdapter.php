<?php
declare(strict_types=1);

namespace TinyAuthBackend\Auth\AclAdapter;

use Cake\Core\Configure;
use Cake\Log\Log;
use Throwable;
use TinyAuth\Auth\AclAdapter\AclAdapterInterface;
use TinyAuth\Auth\AclAdapter\IniAclAdapter;

/**
 * ACL adapter that combines the results of several other adapters into
 * a single merged matrix. See CompositeAllowAdapter for rationale.
 *
 * Configure via `TinyAuth.aclAdapters`:
 *
 * ```php
 * Configure::write('TinyAuth.aclAdapter', CompositeAclAdapter::class);
 * Configure::write('TinyAuth.aclAdapters', [
 *     \TinyAuth\Auth\AclAdapter\IniAclAdapter::class,
 *     \TinyAuthBackend\Auth\AclAdapter\DbAclAdapter::class,
 * ]);
 * ```
 *
 * Default chain when `TinyAuth.aclAdapters` is unset:
 * `[IniAclAdapter, DbAclAdapter]`.
 *
 * **Merge semantics:** within each `plugin.prefix/controller` key,
 * `allow[action]` and `deny[action]` are unioned as `roleAlias => id`
 * maps (later sources add to earlier ones without erasing). The
 * underlying adapters already apply hierarchy expansion where
 * appropriate before the composite merges.
 *
 * **Failure isolation:** if any adapter throws, it is skipped.
 */
class CompositeAclAdapter implements AclAdapterInterface {

	/**
	 * @var array<class-string<\TinyAuth\Auth\AclAdapter\AclAdapterInterface>>
	 */
	protected const DEFAULT_ADAPTERS = [
		IniAclAdapter::class,
		DbAclAdapter::class,
	];

	/**
	 * @param array<string, int> $availableRoles Map of role alias => id.
	 * @param array<string, mixed> $config Current TinyAuth configuration.
	 * @return array<string, array<string, mixed>>
	 */
	public function getAcl(array $availableRoles, array $config): array {
		$configured = Configure::read('TinyAuth.aclAdapters');
		$adapters = $configured === null ? static::DEFAULT_ADAPTERS : (array)$configured;

		$result = [];
		foreach ($adapters as $adapterClass) {
			if (!is_string($adapterClass) || !class_exists($adapterClass)) {
				continue;
			}

			try {
				/** @var \TinyAuth\Auth\AclAdapter\AclAdapterInterface $adapter */
				$adapter = new $adapterClass();
				$contribution = $adapter->getAcl($availableRoles, $config);
			} catch (Throwable $e) {
				Log::warning(sprintf(
					'CompositeAclAdapter skipped delegated adapter "%s" after exception: %s',
					$adapterClass,
					$e->getMessage(),
				));

				continue;
			}

			$result = $this->merge($result, $contribution);
		}

		return $result;
	}

	/**
	 * Union-merge two acl matrices.
	 *
	 * @param array<string, array<string, mixed>> $left
	 * @param array<string, array<string, mixed>> $right
	 * @return array<string, array<string, mixed>>
	 */
	protected function merge(array $left, array $right): array {
		foreach ($right as $key => $rule) {
			if (!isset($left[$key])) {
				$left[$key] = $rule;

				continue;
			}
			foreach (['allow', 'deny'] as $type) {
				$merged = $left[$key][$type] ?? [];
				foreach ($rule[$type] ?? [] as $action => $roleMap) {
					$merged[$action] = ($merged[$action] ?? []) + $roleMap;
				}
				$left[$key][$type] = $merged;
			}
		}

		return $left;
	}

}
