<?php

namespace TestApp;

use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\RouteBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
		// Stand-in for a real CSP middleware: sets a stable `cspNonce` request
		// attribute so RenderedCspComplianceTest exercises the nonce-emitting
		// branch of every `<script nonce="…">` block in the templates.
		$middleware->add(new class implements MiddlewareInterface {
			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
				return $handler->handle($request->withAttribute('cspNonce', 'test-nonce-deadbeef'));
			}
		});
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
