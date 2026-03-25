<?php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::reload();

Router::defaultRouteClass(DashedRoute::class);

// Match the plugin routes from TinyAuthBackendPlugin
// Use prefix scope so fallbacks work correctly with Admin prefix
Router::plugin('TinyAuthBackend', ['path' => '/admin/auth'], function (RouteBuilder $builder): void {
	$builder->prefix('Admin', function (RouteBuilder $prefixBuilder): void {
		$prefixBuilder->connect('/', ['controller' => 'Dashboard', 'action' => 'index']);
		$prefixBuilder->connect('/dashboard', ['controller' => 'Dashboard', 'action' => 'index']);
		$prefixBuilder->connect('/acl', ['controller' => 'Acl', 'action' => 'index']);
		$prefixBuilder->connect('/allow', ['controller' => 'Allow', 'action' => 'index']);
		$prefixBuilder->connect('/roles', ['controller' => 'Roles', 'action' => 'index']);
		$prefixBuilder->connect('/resources', ['controller' => 'Resources', 'action' => 'index']);
		$prefixBuilder->connect('/scopes', ['controller' => 'Scopes', 'action' => 'index']);
		$prefixBuilder->connect('/sync', ['controller' => 'Sync', 'action' => 'controllers']);
		$prefixBuilder->connect('/sync/controllers', ['controller' => 'Sync', 'action' => 'controllers']);
		$prefixBuilder->connect('/sync/resources', ['controller' => 'Sync', 'action' => 'resources']);
		$prefixBuilder->fallbacks();
	});
});
