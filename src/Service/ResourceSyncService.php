<?php
declare(strict_types=1);

namespace TinyAuthBackend\Service;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use DirectoryIterator;
use RegexIterator;

/**
 * Service for scanning app/plugin entities and syncing to database with default abilities.
 */
class ResourceSyncService {

	/**
	 * Default abilities to create for each resource.
	 *
	 * @var array<string>
	 */
	protected array $defaultAbilities = ['view', 'create', 'edit', 'delete'];

	/**
	 * Scan all app and plugin entities.
	 *
	 * @return array<array{name: string, entity_class: string, table_name: string}>
	 */
	public function scan(): array {
		$found = [];
		$appNamespace = (string)(Configure::read('App.namespace') ?: 'App');

		// Scan app entities
		$found = array_merge($found, $this->scanPath(APP . 'Model' . DS . 'Entity' . DS, $appNamespace));

		// Scan plugin entities
		$plugins = Plugin::loaded();
		foreach ($plugins as $plugin) {
			$path = Plugin::path($plugin) . 'src' . DS . 'Model' . DS . 'Entity' . DS;
			if (is_dir($path)) {
				$found = array_merge($found, $this->scanPath($path, $plugin));
			}
		}

		return $found;
	}

	/**
	 * Scan a directory for entity classes.
	 *
	 * @param string $path The directory path to scan.
	 * @param string $namespace The namespace (plugin name or 'App').
	 * @return array<array{name: string, entity_class: string, table_name: string}>
	 */
	protected function scanPath(string $path, string $namespace): array {
		$found = [];

		if (!is_dir($path)) {
			return $found;
		}

		$iterator = new DirectoryIterator($path);
		$files = new RegexIterator($iterator, '/^.*\.php$/');

		foreach ($files as $file) {
			$entityName = str_replace('.php', '', $file->getFilename());
			$className = $namespace . '\\Model\\Entity\\' . $entityName;

			if (!class_exists($className)) {
				continue;
			}

			// Derive table name
			$tableName = Inflector::tableize($entityName);

			$found[] = [
				'name' => $entityName,
				'entity_class' => $className,
				'table_name' => $tableName,
			];
		}

		return $found;
	}

	/**
	 * Sync scanned entities to the database as resources.
	 *
	 * @param array<string, mixed> $options Sync options:
	 *   - addNew: Whether to add new resources (default: true)
	 *   - addDefaultAbilities: Whether to add default abilities (default: true)
	 * @return array{added: int, abilities_added: int}
	 */
	public function sync(array $options = []): array {
		$addNew = $options['addNew'] ?? true;
		$addDefaultAbilities = $options['addDefaultAbilities'] ?? true;

		$resourcesTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Resources');
		$abilitiesTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.ResourceAbilities');

		$scanned = $this->scan();
		$result = ['added' => 0, 'abilities_added' => 0];

		foreach ($scanned as $item) {
			$existing = $resourcesTable->find()
				->where(['entity_class' => $item['entity_class']])
				->first();

			if (!$existing && $addNew) {
				$resource = $resourcesTable->newEntity([
					'name' => $item['name'],
					'entity_class' => $item['entity_class'],
					'table_name' => $item['table_name'],
				]);
				if ($resourcesTable->save($resource)) {
					$existing = $resource;
					$result['added']++;
				}
			}

			if ($existing && $addDefaultAbilities) {
				foreach ($this->defaultAbilities as $abilityName) {
					$existingAbility = $abilitiesTable->find()
						->where([
							'resource_id' => $existing->id,
							'name' => $abilityName,
						])
						->first();

					if (!$existingAbility) {
						$ability = $abilitiesTable->newEntity([
							'resource_id' => $existing->id,
							'name' => $abilityName,
						]);
						if ($abilitiesTable->save($ability)) {
							$result['abilities_added']++;
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Get the default abilities.
	 *
	 * @return array<string>
	 */
	public function getDefaultAbilities(): array {
		return $this->defaultAbilities;
	}

	/**
	 * Set custom default abilities.
	 *
	 * @param array<string> $abilities The abilities to use.
	 * @return void
	 */
	public function setDefaultAbilities(array $abilities): void {
		$this->defaultAbilities = $abilities;
	}

}
