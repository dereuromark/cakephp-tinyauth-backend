<?php
declare(strict_types=1);

namespace TinyAuthBackend\Auth\AllowAdapter;

use Cake\Core\Configure;
use Cake\Log\Log;
use Throwable;
use TinyAuth\Auth\AllowAdapter\AllowAdapterInterface;
use TinyAuth\Auth\AllowAdapter\IniAllowAdapter;

/**
 * Allow adapter that combines the results of several other adapters
 * into a single merged matrix.
 *
 * The canonical use case is an incremental adoption of
 * tinyauth-backend in an existing app: keep the main application's
 * legacy `auth_allow.ini` rules untouched and layer the new
 * DB-backed rules on top, all served by a single adapter class
 * (since TinyAuth only supports one adapter per slot and caches the
 * result app-wide).
 *
 * Configure via `TinyAuth.allowAdapters`:
 *
 * ```php
 * Configure::write('TinyAuth.allowAdapter', CompositeAllowAdapter::class);
 * Configure::write('TinyAuth.allowAdapters', [
 *     \TinyAuth\Auth\AllowAdapter\IniAllowAdapter::class,
 *     \TinyAuthBackend\Auth\AllowAdapter\DbAllowAdapter::class,
 * ]);
 * ```
 *
 * If `TinyAuth.allowAdapters` is not set, the default chain is
 * `[IniAllowAdapter, DbAllowAdapter]` — the most common gradual-
 * adoption scenario.
 *
 * **Merge semantics:** for every key that appears in multiple
 * sources, the `allow` and `deny` lists are *unioned*. An action
 * public in either source becomes public in the merged result. This
 * is intentional: the composite is additive.
 *
 * **Failure isolation:** if any adapter throws (e.g. DB not yet
 * migrated on first install), it is skipped with its contribution
 * treated as empty. This lets the app still boot via the remaining
 * adapters.
 */
class CompositeAllowAdapter implements AllowAdapterInterface {

	/**
	 * Default adapter chain when `TinyAuth.allowAdapters` is unset.
	 *
	 * @var array<class-string<\TinyAuth\Auth\AllowAdapter\AllowAdapterInterface>>
	 */
	protected const DEFAULT_ADAPTERS = [
		IniAllowAdapter::class,
		DbAllowAdapter::class,
	];

	/**
	 * @param array<string, mixed> $config Current TinyAuth configuration.
	 * @return array<string, array<string, mixed>>
	 */
	public function getAllow(array $config): array {
		$configured = Configure::read('TinyAuth.allowAdapters');
		$adapters = $configured === null ? static::DEFAULT_ADAPTERS : (array)$configured;

		$result = [];
		foreach ($adapters as $adapterClass) {
			if (!is_string($adapterClass) || !class_exists($adapterClass)) {
				continue;
			}

			try {
				/** @var \TinyAuth\Auth\AllowAdapter\AllowAdapterInterface $adapter */
				$adapter = new $adapterClass();
				$contribution = $adapter->getAllow($config);
			} catch (Throwable $e) {
				$shortName = strrchr($adapterClass, '\\');
				Log::warning(sprintf(
					'CompositeAllowAdapter skipped delegated adapter "%s" after %s',
					$shortName !== false ? substr($shortName, 1) : $adapterClass,
					$e::class,
				));

				continue;
			}

			$result = $this->merge($result, $contribution);
		}

		return $result;
	}

	/**
	 * Union-merge two allow matrices keyed by `plugin.prefix/controller`.
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
			$left[$key]['allow'] = array_values(array_unique(array_merge(
				$left[$key]['allow'] ?? [],
				$rule['allow'] ?? [],
			)));
			$left[$key]['deny'] = array_values(array_unique(array_merge(
				$left[$key]['deny'] ?? [],
				$rule['deny'] ?? [],
			)));
		}

		return $left;
	}

}
