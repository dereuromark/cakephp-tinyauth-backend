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
	 * @param \Cake\Core\PluginApplicationInterface<\Cake\Core\HttpApplicationInterface> $app The application instance
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
		$routes->plugin('TinyAuthBackend', ['path' => '/admin/tinyauth'], function (RouteBuilder $builder): void {
			$builder->prefix('Admin', function (RouteBuilder $prefixBuilder): void {
				$prefixBuilder->connect('/', ['controller' => 'Acl', 'action' => 'index']);
				$prefixBuilder->connect('/acl', ['controller' => 'Acl', 'action' => 'index']);
				$prefixBuilder->connect('/allow', ['controller' => 'Allow', 'action' => 'index']);
				$prefixBuilder->connect('/roles', ['controller' => 'Roles', 'action' => 'index']);
				$prefixBuilder->connect('/resources', ['controller' => 'Resources', 'action' => 'index']);
				$prefixBuilder->connect('/scopes', ['controller' => 'Scopes', 'action' => 'index']);
				$prefixBuilder->connect('/sync', ['controller' => 'Sync', 'action' => 'index']);
				$prefixBuilder->connect('/sync/controllers', ['controller' => 'Sync', 'action' => 'controllers']);
				$prefixBuilder->connect('/sync/resources', ['controller' => 'Sync', 'action' => 'resources']);
				$prefixBuilder->fallbacks();
			});
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
