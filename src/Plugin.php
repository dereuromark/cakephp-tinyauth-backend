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
	public function routes($routes) {
		/*
		// This would be without admin prefix
		$routes->plugin(
			'TinyAuthBackend',
			['path' => '/auth'],
			function (RouteBuilder $routes) {
				$routes->connect('/', ['controller' => 'Auth', 'action' => 'index']);
				$routes->fallbacks();
			}
		);
		*/

		$routes->prefix('admin', function (RouteBuilder $routes) {
			$routes->plugin(
				'TinyAuthBackend',
				['path' => '/auth'],
				function (RouteBuilder $routes) {
					$routes->connect('/', ['controller' => 'Auth', 'action' => 'index']);
					$routes->fallbacks();
				}
			);
		});
	}

}
