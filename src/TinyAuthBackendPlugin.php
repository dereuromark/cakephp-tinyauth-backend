<?php
declare(strict_types=1);

namespace TinyAuthBackend;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\PluginApplicationInterface;
use Cake\Routing\RouteBuilder;
use TinyAuthBackend\Command\ImportCommand;
use TinyAuthBackend\Command\InitCommand;

/**
 * Plugin for TinyAuthBackend
 */
class TinyAuthBackendPlugin extends BasePlugin {

	/**
	 * @var bool
	 */
	protected bool $middlewareEnabled = false;

	/**
	 * @var bool
	 */
	protected bool $bootstrapEnabled = true;

	/**
	 * @var bool
	 */
	protected bool $routesEnabled = true;

	/**
	 * @param \Cake\Core\PluginApplicationInterface $app The application instance
	 * @return void
	 */
	public function bootstrap(PluginApplicationInterface $app): void {
		parent::bootstrap($app);
	}

	/**
	 * @param \Cake\Routing\RouteBuilder $routes The route builder to update.
	 * @return void
	 */
	public function routes(RouteBuilder $routes): void {
		$routes->plugin('TinyAuthBackend', ['path' => '/admin/tinyauth'], function (RouteBuilder $builder) {
			$builder->connect('/', ['controller' => 'Acl', 'action' => 'index', 'prefix' => 'Admin']);
			$builder->connect('/acl', ['controller' => 'Acl', 'action' => 'index', 'prefix' => 'Admin']);
			$builder->connect('/allow', ['controller' => 'Allow', 'action' => 'index', 'prefix' => 'Admin']);
			$builder->connect('/roles', ['controller' => 'Roles', 'action' => 'index', 'prefix' => 'Admin']);
			$builder->connect('/resources', ['controller' => 'Resources', 'action' => 'index', 'prefix' => 'Admin']);
			$builder->connect('/scopes', ['controller' => 'Scopes', 'action' => 'index', 'prefix' => 'Admin']);
			$builder->connect('/sync', ['controller' => 'Sync', 'action' => 'index', 'prefix' => 'Admin']);
			$builder->fallbacks();
		});
	}

	/**
	 * Console hook
	 *
	 * @param \Cake\Console\CommandCollection $commands The command collection
	 * @return \Cake\Console\CommandCollection
	 */
	public function console(CommandCollection $commands): CommandCollection {
		$commands->add('tiny_auth_backend init', InitCommand::class);
		$commands->add('tiny_auth_backend import', ImportCommand::class);

		return $commands;
	}

}
