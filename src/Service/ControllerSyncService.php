<?php
declare(strict_types=1);

namespace TinyAuthBackend\Service;

use Cake\Core\Plugin;
use Cake\Filesystem\Folder;
use Cake\ORM\TableRegistry;
use ReflectionClass;
use ReflectionMethod;

/**
 * Service for scanning app/plugin controllers and syncing to database.
 */
class ControllerSyncService {

	/**
	 * Methods to exclude from action list.
	 *
	 * @var array<string>
	 */
	protected array $excludedMethods = [
		'initialize',
		'beforeFilter',
		'afterFilter',
		'beforeRender',
		'beforeRedirect',
		'invokeAction',
		'redirect',
		'render',
		'set',
		'setRequest',
		'components',
		'modelFactory',
		'loadComponent',
		'loadModel',
		'fetchTable',
		'getName',
		'setName',
		'getPlugin',
		'setPlugin',
		'getRequest',
		'getResponse',
		'setResponse',
		'getEventManager',
		'setEventManager',
	];

	/**
	 * Scan all app and plugin controllers.
	 *
	 * @return array<array{plugin: string|null, prefix: string|null, name: string, actions: array<string>}>
	 */
	public function scan(): array {
		$found = [];

		// Scan app controllers
		$found = array_merge($found, $this->scanPath(APP . 'Controller' . DS, null));

		// Scan plugin controllers
		$plugins = Plugin::loaded();
		foreach ($plugins as $plugin) {
			$path = Plugin::path($plugin) . 'src' . DS . 'Controller' . DS;
			if (is_dir($path)) {
				$found = array_merge($found, $this->scanPath($path, $plugin));
			}
		}

		return $found;
	}

	/**
	 * Scan a directory for controllers.
	 *
	 * @param string $path The directory path to scan.
	 * @param string|null $plugin The plugin name or null for app.
	 * @param string|null $prefix The controller prefix (for subdirectories).
	 * @return array<array{plugin: string|null, prefix: string|null, name: string, actions: array<string>}>
	 */
	protected function scanPath(string $path, ?string $plugin, ?string $prefix = null): array {
		$found = [];

		if (!is_dir($path)) {
			return $found;
		}

		$folder = new Folder($path);
		$files = $folder->find('.*Controller\.php');

		foreach ($files as $file) {
			$controllerName = str_replace('Controller.php', '', $file);
			if ($controllerName === 'App') {
				continue;
			}

			$className = $this->buildClassName($controllerName, $plugin, $prefix);
			if (!class_exists($className)) {
				continue;
			}

			$actions = $this->getControllerActions($className);
			$found[] = [
				'plugin' => $plugin,
				'prefix' => $prefix,
				'name' => $controllerName,
				'actions' => $actions,
			];
		}

		// Scan subdirectories as prefixes
		$subDirs = $folder->subdirectories();
		foreach ($subDirs as $subDir) {
			$subPrefix = basename($subDir);
			$newPrefix = $prefix ? $prefix . '/' . $subPrefix : $subPrefix;
			$found = array_merge($found, $this->scanPath($subDir . DS, $plugin, $newPrefix));
		}

		return $found;
	}

	/**
	 * Build a fully qualified class name for a controller.
	 *
	 * @param string $controller The controller name (without 'Controller' suffix).
	 * @param string|null $plugin The plugin name or null for app.
	 * @param string|null $prefix The controller prefix.
	 * @return string The fully qualified class name.
	 */
	protected function buildClassName(string $controller, ?string $plugin, ?string $prefix): string {
		$namespace = $plugin ? $plugin . '\\' : 'App\\';
		$namespace .= 'Controller\\';
		if ($prefix) {
			$namespace .= str_replace('/', '\\', $prefix) . '\\';
		}

		return $namespace . $controller . 'Controller';
	}

	/**
	 * Get public action methods from a controller class.
	 *
	 * @param string $className The fully qualified controller class name.
	 * @return array<string> Array of action method names.
	 */
	protected function getControllerActions(string $className): array {
		$actions = [];
		$reflection = new ReflectionClass($className);
		$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

		foreach ($methods as $method) {
			// Skip inherited methods and internal methods
			if ($method->class !== $className) {
				continue;
			}
			if (str_starts_with($method->name, '_')) {
				continue;
			}
			if (in_array($method->name, $this->excludedMethods, true)) {
				continue;
			}

			$actions[] = $method->name;
		}

		return $actions;
	}

	/**
	 * Sync scanned controllers to the database.
	 *
	 * @param array<string, mixed> $options Sync options:
	 *   - addNew: Whether to add new controllers (default: true)
	 *   - addActions: Whether to add new actions (default: true)
	 * @return array{added: int, updated: int, actions_added: int}
	 */
	public function sync(array $options = []): array {
		$addNew = $options['addNew'] ?? true;
		$addActions = $options['addActions'] ?? true;

		$controllersTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.TinyauthControllers');
		$actionsTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.Actions');

		$scanned = $this->scan();
		$result = ['added' => 0, 'updated' => 0, 'actions_added' => 0];

		foreach ($scanned as $item) {
			$existing = $controllersTable->find()
				->where([
					'plugin IS' => $item['plugin'],
					'prefix IS' => $item['prefix'],
					'name' => $item['name'],
				])
				->first();

			if (!$existing && $addNew) {
				$controller = $controllersTable->newEntity([
					'plugin' => $item['plugin'],
					'prefix' => $item['prefix'],
					'name' => $item['name'],
				]);
				$controllersTable->save($controller);
				$existing = $controller;
				$result['added']++;
			}

			if ($existing && $addActions) {
				foreach ($item['actions'] as $actionName) {
					$existingAction = $actionsTable->find()
						->where([
							'controller_id' => $existing->id,
							'name' => $actionName,
						])
						->first();

					if (!$existingAction) {
						$action = $actionsTable->newEntity([
							'controller_id' => $existing->id,
							'name' => $actionName,
							'is_public' => false,
						]);
						$actionsTable->save($action);
						$result['actions_added']++;
					}
				}
			}
		}

		return $result;
	}

}
