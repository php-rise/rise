<?php
namespace Rise\Test\Middlewares;

use PHPUnit\Framework\TestCase;
use Rise\Middlewares\Router as RouterMiddleware;
use Rise\Router as RouterService;
use Rise\Router\Dispatcher;

final class RouterTest extends TestCase {
	public function testRun() {
		$router = $this->createMock(RouterService::class);
		$dispatcher = $this->createMock(Dispatcher::class);

		$executedNext = false;
		$next = function () use (&$executedNext) {
			$executedNext = true;
		};

		$router->expects($this->once())
			->method('buildRoutes')
			->will($this->returnSelf());

		$router->expects($this->once())
			->method('match')
			->willReturn(['Handler.handle']);

		$dispatcher->expects($this->once())
			->method('setHandlers')
			->with($this->equalTo(['Handler.handle']))
			->will($this->returnSelf());

		$dispatcher->expects($this->once())
			->method('dispatch')
			->will($this->returnSelf());

		$middleware = new RouterMiddleware($router, $dispatcher);
		$middleware->run($next);

		$this->assertTrue($executedNext);
	}
}
