<?php

namespace TinyAuthBackend;

use Cake\Core\BasePlugin;
use Cake\Routing\RouteBuilder;

/**
 * Plugin for TinyAuthBackend
 */
class Plugin extends BasePlugin {

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

}
