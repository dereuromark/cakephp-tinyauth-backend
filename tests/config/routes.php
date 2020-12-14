<?php

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::reload();

Router::defaultRouteClass(DashedRoute::class);

// Copy from Application.php for now
Router::prefix('Admin', function (RouteBuilder $routes) {
	$routes->plugin(
		'TinyAuthBackend',
		['path' => '/auth'],
		function (RouteBuilder $routes) {
			$routes->connect('/', ['controller' => 'Auth', 'action' => 'index']);
			$routes->fallbacks();
		}
	);
});
