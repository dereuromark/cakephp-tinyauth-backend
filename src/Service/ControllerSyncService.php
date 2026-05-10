<?php
declare(strict_types=1);

namespace TinyAuthBackend\Service;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\ORM\TableRegistry;
use DirectoryIterator;
use ReflectionClass;
use ReflectionMethod;
use RegexIterator;

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
		$appNamespace = (string)(Configure::read('App.namespace') ?: 'App');

		// Scan app controllers
		$found = array_merge($found, $this->scanPath(APP . 'Controller' . DS, $appNamespace));

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

		// Find controller files
		$iterator = new DirectoryIterator($path);
		$files = new RegexIterator($iterator, '/^.*Controller\.php$/');

		foreach ($files as $file) {
			$controllerName = str_replace('Controller.php', '', $file->getFilename());
			if ($controllerName === 'App') {
				continue;
			}

			$className = $this->buildClassName($controllerName, $plugin, $prefix);
			if (!class_exists($className)) {
				continue;
			}

			/** @var class-string $className */
			$actions = $this->getControllerActions($className);
			$found[] = [
				'plugin' => $plugin,
				'prefix' => $prefix,
				'name' => $controllerName,
				'actions' => $actions,
			];
		}

		// Scan subdirectories as prefixes
		$subDirs = $this->getSubdirectories($path);
		foreach ($subDirs as $subDir) {
			$subPrefix = basename($subDir);
			$newPrefix = $prefix ? $prefix . '/' . $subPrefix : $subPrefix;
			$found = array_merge($found, $this->scanPath($subDir . DS, $plugin, $newPrefix));
		}

		return $found;
	}

	/**
	 * Get subdirectories of a path.
	 *
	 * @param string $path The path to scan.
	 * @return array<string> Array of subdirectory paths.
	 */
	protected function getSubdirectories(string $path): array {
		$subDirs = [];
		$iterator = new DirectoryIterator($path);

		foreach ($iterator as $item) {
			if ($item->isDir() && !$item->isDot()) {
				$subDirs[] = $item->getPathname();
			}
		}

		return $subDirs;
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
		$appNamespace = (string)(Configure::read('App.namespace') ?: 'App');
		$namespace = $plugin ? $plugin . '\\' : $appNamespace . '\\';
		$namespace .= 'Controller\\';
		if ($prefix) {
			$namespace .= str_replace('/', '\\', $prefix) . '\\';
		}

		return $namespace . $controller . 'Controller';
	}

	/**
	 * Get public action methods from a controller class.
	 *
	 * @param class-string $className The fully qualified controller class name.
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

		// Pre-load all existing controllers and actions into hashmaps so the per-row
		// loop below is constant-time on lookup. The previous implementation issued
		// one SELECT per scanned controller and one per action, producing ~600
		// round-trips for a 100-controller / 5-action-each app.
		/** @var array<string, \TinyAuthBackend\Model\Entity\TinyauthController> $existingByKey */
		$existingByKey = [];
		foreach ($controllersTable->find()->all() as $row) {
			/** @var \TinyAuthBackend\Model\Entity\TinyauthController $row */
			$existingByKey[$this->controllerKey($row->plugin, $row->prefix, $row->name)] = $row;
		}

		/** @var array<int, array<string, true>> $actionsByControllerId */
		$actionsByControllerId = [];
		foreach ($actionsTable->find()->select(['controller_id', 'name'])->all() as $action) {
			/** @var \TinyAuthBackend\Model\Entity\Action $action */
			$actionsByControllerId[$action->controller_id][$action->name] = true;
		}

		foreach ($scanned as $item) {
			$key = $this->controllerKey($item['plugin'], $item['prefix'], $item['name']);
			$existing = $existingByKey[$key] ?? null;

			if (!$existing && $addNew) {
				$controller = $controllersTable->newEntity([
					'plugin' => $item['plugin'],
					'prefix' => $item['prefix'],
					'name' => $item['name'],
				]);
				if ($controllersTable->save($controller)) {
					$existing = $controller;
					$existingByKey[$key] = $controller;
					$result['added']++;
				}
			}

			if ($existing && $existing->get('id') && $addActions) {
				$existingId = (int)$existing->get('id');
				$existingActionNames = $actionsByControllerId[$existingId] ?? [];

				foreach ($item['actions'] as $actionName) {
					if (isset($existingActionNames[$actionName])) {
						continue;
					}

					$action = $actionsTable->newEntity([
						'controller_id' => $existingId,
						'name' => $actionName,
						'is_public' => false,
					]);
					if ($actionsTable->save($action)) {
						$result['actions_added']++;
						$actionsByControllerId[$existingId][$actionName] = true;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Build a stable lookup key for a controller across plugin/prefix/name.
	 *
	 * Null-safe: uses an empty string for missing components to keep the key
	 * deterministic across PHP versions.
	 *
	 * @param string|null $plugin
	 * @param string|null $prefix
	 * @param string $name
	 * @return string
	 */
	protected function controllerKey(?string $plugin, ?string $prefix, string $name): string {
		return ($plugin ?? '') . "\x00" . ($prefix ?? '') . "\x00" . $name;
	}

}
