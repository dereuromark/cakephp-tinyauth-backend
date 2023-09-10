<?php

namespace TestApp;

use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\RouteBuilder;

class Application extends BaseApplication {

	/**
	 * @param \Cake\Routing\RouteBuilder $routes
	 *
	 * @return void
	 */
	public function routes(RouteBuilder $routes): void {
	}

	/**
	 * @param \Cake\Http\MiddlewareQueue $middleware The middleware queue to set in your App Class
	 * @return \Cake\Http\MiddlewareQueue
	 */
	public function middleware(MiddlewareQueue $middleware): MiddlewareQueue {
		$middleware->add(new RoutingMiddleware($this));

		return $middleware;
	}

	/**
	 * @return void
	 */
	public function bootstrap(): void {
		$this->addPlugin('TinyAuth');
	}

}
