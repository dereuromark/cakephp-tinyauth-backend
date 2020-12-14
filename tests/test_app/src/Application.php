<?php

namespace TestApp;

use Cake\Http\BaseApplication;
use Cake\Routing\Middleware\RoutingMiddleware;

class Application extends BaseApplication {

	/**
	 * @param \Cake\Http\MiddlewareQueue $middleware The middleware queue to set in your App Class
	 * @return \Cake\Http\MiddlewareQueue
	 */
	public function middleware(\Cake\Http\MiddlewareQueue $middleware): \Cake\Http\MiddlewareQueue
	{
		$middleware->add(new RoutingMiddleware($this));

		return $middleware;
	}

	/**
	 * @return void
	 */
	public function bootstrap(): void {
		parent::bootstrap();

		$this->addPlugin('TinyAuth');
	}

}
