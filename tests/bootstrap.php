<?php
/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Routing\Router;
use TestApp\Controller\AppController;

require dirname(__DIR__) . '/vendor/cakephp/cakephp/src/basics.php';
require dirname(__DIR__) . '/vendor/autoload.php';

define('ROOT', dirname(__DIR__));
define('APP_DIR', 'src');

define('APP', rtrim(sys_get_temp_dir(), DS) . DS . APP_DIR . DS);
if (!is_dir(APP)) {
	mkdir(APP, 0770, true);
}
define('TESTS', ROOT . DS . 'tests' . DS);

define('CONFIG', dirname(__FILE__) . DS . 'config' . DS);

define('TMP', ROOT . DS . 'tmp' . DS);
if (!is_dir(TMP)) {
	mkdir(TMP, 0770, true);
}

define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);

define('CAKE_CORE_INCLUDE_PATH', ROOT . '/vendor/cakephp/cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);

Configure::write('App', [
	'namespace' => 'TestApp',
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
	'_cake_core_' => [
		'className' => 'File',
		'prefix' => 'crud_myapp_cake_core_',
		'path' => CACHE . 'persistent/',
		'serialize' => true,
		'duration' => '+10 seconds',
	],
	'_cake_model_' => [
		'className' => 'File',
		'prefix' => 'crud_my_app_cake_model_',
		'path' => CACHE . 'models/',
		'serialize' => 'File',
		'duration' => '+10 seconds',
	],
];

Cache::setConfig($cache);

Plugin::getCollection()->add(new TinyAuthBackend\Plugin());

Router::reload();

class_alias(AppController::class, 'App\Controller\AppController');

define('ROLE_USER', 1);
define('ROLE_MODERATOR', 2);
define('ROLE_ADMIN', 3);

// Ensure default test connection is defined
if (!getenv('db_class')) {
	putenv('db_class=Cake\Database\Driver\Sqlite');
	putenv('db_dsn=sqlite::memory:');
}

ConnectionManager::setConfig('test', [
	'className' => 'Cake\Database\Connection',
	'driver' => getenv('db_class') ?: null,
	'dsn' => getenv('db_dsn') ?: null,
	//'database' => getenv('db_database'),
	//'username' => getenv('db_username'),
	//'password' => getenv('db_password'),
	'timezone' => 'UTC',
	'quoteIdentifiers' => true,
	'cacheMetadata' => true,
]);
