<?php
declare(strict_types=1);

namespace TinyAuthBackend\Service;

use Cake\Core\Configure;
use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;
use Exception;

/**
 * Service for detecting and managing available features.
 *
 * Uses hybrid approach: auto-detect from DB tables, allow config overrides.
 * Special handling for 'roles' - also considers external roleSource config.
 */
class FeatureService {

	/**
	 * Cached features.
	 *
	 * @var array<string, bool>|null
	 */
	protected static ?array $cachedFeatures = null;

	/**
	 * @var \TinyAuthBackend\Service\RoleSourceService
	 */
	protected RoleSourceService $roleSource;

	/**
	 * Feature to table mapping.
	 *
	 * @var array<string, string>
	 */
	protected const FEATURE_TABLES = [
		'acl' => 'tinyauth_acl_permissions',
		'allow' => 'tinyauth_actions',
		'roles' => 'tinyauth_roles',
		'resources' => 'tinyauth_resources',
	];

	/**
	 * Constructor.
	 *
	 * @param \TinyAuthBackend\Service\RoleSourceService|null $roleSource Optional role source service.
	 */
	public function __construct(?RoleSourceService $roleSource = null) {
		$this->roleSource = $roleSource ?? new RoleSourceService();
	}

	/**
	 * Check if a feature is enabled.
	 *
	 * @param string $feature The feature name.
	 * @return bool Whether the feature is enabled.
	 */
	public function isEnabled(string $feature): bool {
		$features = $this->getEnabledFeatures();

		return $features[$feature] ?? false;
	}

	/**
	 * Get all enabled features.
	 *
	 * @return array<string, bool>
	 */
	public function getEnabledFeatures(): array {
		if (static::$cachedFeatures !== null) {
			return static::$cachedFeatures;
		}

		$configFeatures = Configure::read('TinyAuthBackend.features') ?? [];
		$result = [];

		foreach (static::FEATURE_TABLES as $feature => $table) {
			$configValue = $configFeatures[$feature] ?? null;

			if ($configValue === true) {
				// Force enabled
				$result[$feature] = true;
			} elseif ($configValue === false) {
				// Force disabled
				$result[$feature] = false;
			} else {
				// Auto-detect
				if ($feature === 'roles') {
					// Special handling: roles available from external source OR table
					$hasExternalSource = Configure::read('TinyAuthBackend.roleSource') !== null;
					$result[$feature] = $hasExternalSource || $this->tableExists($table);
				} else {
					$result[$feature] = $this->tableExists($table);
				}
			}
		}

		static::$cachedFeatures = $result;

		return $result;
	}

	/**
	 * Check if the Roles feature has a manageable UI (vs read-only external source).
	 *
	 * @return bool Whether roles are managed by this plugin.
	 */
	public function isRolesManaged(): bool {
		return $this->roleSource->isManaged();
	}

	/**
	 * Get features formatted for UI navigation.
	 *
	 * @return array<array{name: string, label: string, enabled: bool, route: array<string, string>}>
	 */
	public function getNavigationItems(): array {
		$features = $this->getEnabledFeatures();

		$items = [
			[
				'name' => 'acl',
				'label' => 'ACL',
				'enabled' => $features['acl'] ?? false,
				'route' => ['controller' => 'Acl', 'action' => 'index'],
			],
			[
				'name' => 'allow',
				'label' => 'Allow',
				'enabled' => $features['allow'] ?? false,
				'route' => ['controller' => 'Allow', 'action' => 'index'],
			],
			[
				'name' => 'roles',
				'label' => 'Roles',
				'enabled' => $features['roles'] ?? false,
				'route' => ['controller' => 'Roles', 'action' => 'index'],
			],
			[
				'name' => 'resources',
				'label' => 'Resources',
				'enabled' => $features['resources'] ?? false,
				'route' => ['controller' => 'Resources', 'action' => 'index'],
			],
		];

		return array_filter($items, fn ($item) => $item['enabled']);
	}

	/**
	 * Clear the cached features (for testing or after migrations).
	 *
	 * @return void
	 */
	public function clearCache(): void {
		static::$cachedFeatures = null;
	}

	/**
	 * Check if a table exists in the database.
	 *
	 * @param string $tableName The table name to check.
	 * @return bool Whether the table exists.
	 */
	protected function tableExists(string $tableName): bool {
		try {
			$connection = ConnectionManager::get('default');
			if (!$connection instanceof Connection) {
				return false;
			}
			$tables = $connection->getSchemaCollection()->listTables();

			return in_array($tableName, $tables, true);
		} catch (Exception $e) {
			// Treat connection errors as "table missing" - conservative approach
			// Logging could be added here if debugging is needed
			return false;
		}
	}

}
