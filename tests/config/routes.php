<?php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

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
		},
	);
});
