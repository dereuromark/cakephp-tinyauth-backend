<?php

use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::reload();

Router::defaultRouteClass(DashedRoute::class);
/*
Router::prefix('admin', function (RouteBuilder $routes) {
	$routes->plugin(
		'TinyAuthBackend',
		['path' => '/auth'],
		function (RouteBuilder $routes) {
			$routes->connect('/', ['controller' => 'Auth', 'action' => 'index']);
			$routes->fallbacks();
		}
	);
});
*/
