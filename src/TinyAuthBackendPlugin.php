<?php

namespace TinyAuthBackend;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Routing\RouteBuilder;
use TinyAuthBackend\Command\ImportCommand;
use TinyAuthBackend\Command\InitCommand;

/**
 * Plugin for TinyAuthBackend
 */
class TinyAuthBackendPlugin extends BasePlugin {

	protected bool $bootstrapEnabled = false;

	/**
	 * @param \Cake\Routing\RouteBuilder $routes The route builder to update.
	 * @return void
	 */
	public function routes(RouteBuilder $routes): void {
		$routes->prefix('Admin', function (RouteBuilder $routes): void {
			$routes->plugin(
				'TinyAuthBackend',
				['path' => '/auth'],
				function (RouteBuilder $routes): void {
					$routes->connect('/', ['controller' => 'Auth', 'action' => 'index']);
					$routes->fallbacks();
				},
			);
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
