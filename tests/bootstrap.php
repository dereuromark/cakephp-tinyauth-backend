<?php
/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Routing\Router;
use Cake\TestSuite\Fixture\SchemaLoader;
use TestApp\Controller\AppController;
use TinyAuthBackend\TinyAuthBackendPlugin;

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

if (!function_exists('findVendorPath')) {
	/**
	 * @param string $startDir
	 * @return string|null
	 */
	function findVendorPath(string $startDir): ?string {
		$dir = $startDir;
		while ($dir !== dirname($dir)) {
			$autoload = $dir . DS . 'vendor' . DS . 'autoload.php';
			if (file_exists($autoload)) {
				return $dir . DS . 'vendor';
			}
			$dir = dirname($dir);
		}

		return null;
	}
}

define('ROOT', dirname(__DIR__));
define('APP_DIR', 'src');

define('APP', rtrim(sys_get_temp_dir(), DS) . DS . APP_DIR . DS);
if (!is_dir(APP)) {
	mkdir(APP, 0770, true);
}

define('TMP', ROOT . DS . 'tmp' . DS);
if (!is_dir(TMP)) {
	mkdir(TMP, 0770, true);
}
define('CONFIG', ROOT . DS . 'config' . DS);
define('TESTS', ROOT . DS . 'tests' . DS);

define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);

$vendorPath = findVendorPath(ROOT);
if ($vendorPath === null) {
	throw new RuntimeException('Unable to locate Composer vendor directory for tests.');
}

define('CAKE_CORE_INCLUDE_PATH', $vendorPath . '/cakephp/cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . APP_DIR . DS);

$autoloader = require $vendorPath . '/autoload.php';
$autoloader->addPsr4('TinyAuthBackend\\Test\\', ROOT . DS . 'tests' . DS);
$autoloader->addPsr4('TestApp\\', ROOT . DS . 'tests' . DS . 'test_app' . DS . 'src' . DS);

require CORE_PATH . 'config/bootstrap.php';
require CAKE_CORE_INCLUDE_PATH . '/src/functions.php';

Configure::write('App', [
	'namespace' => 'TestApp',
	'encoding' => 'UTF-8',
	'paths' => [
		'templates' => [ROOT . DS . 'tests' . DS . 'test_app' . DS . 'templates' . DS],
	],
]);

Configure::write('debug', true);

$cache = [
	'default' => [
		'engine' => 'File',
		'path' => CACHE,
	],
	'_cake_translations_' => [
		'className' => 'File',
		'prefix' => 'myapp_cake_translations_',
		'path' => CACHE . 'persistent/',
		'serialize' => true,
		'duration' => '+10 seconds',
	],
	'_cake_model_' => [
		'className' => 'File',
		'prefix' => 'myapp_cake_model_',
		'path' => CACHE . 'models/',
		'serialize' => 'File',
		'duration' => '+10 seconds',
	],
];

Cache::setConfig($cache);

Plugin::getCollection()->add(new TinyAuthBackendPlugin());

Router::reload();

class_alias(AppController::class, 'App\Controller\AppController');

define('ROLE_USER', 1);
define('ROLE_MODERATOR', 2);
define('ROLE_ADMIN', 3);

// Ensure default test connection is defined
if (!getenv('DB_URL')) {
	putenv('DB_URL=sqlite:///:memory:');
}

ConnectionManager::setConfig('test', [
	'url' => getenv('DB_URL') ?: null,
	//'database' => getenv('db_database'),
	//'username' => getenv('db_username'),
	//'password' => getenv('db_password'),
	'timezone' => 'UTC',
	'quoteIdentifiers' => true,
	'cacheMetadata' => true,
]);

if (env('FIXTURE_SCHEMA_METADATA')) {
	$loader = new SchemaLoader();
	$loader->loadInternalFile(env('FIXTURE_SCHEMA_METADATA'));
}
