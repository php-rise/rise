<?php
namespace Rise\Middlewares;

use Closure;
use Rise\Router as RouterService;
use Rise\Router\Dispatcher;

class Router {
	/**
	 * @var \Rise\Router
	 */
	protected $router;

	/**
	 * @var \Rise\Router\Dispatcher
	 */
	protected $dispatcher;

	public function __construct(RouterService $router, Dispatcher $dispatcher) {
		$this->router = $router;
		$this->dispatcher = $dispatcher;
	}

	public function run(Closure $next) {
		$handlers = $this->router->buildRoutes()->match();
		$this->dispatcher->setHandlers($handlers)->dispatch();
		$next();
	}
}
