<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use Rise\Path;
use Rise\Router;
use Rise\Http\Response;
use Rise\Session;
use Rise\Container\DynamicFactory;
use Rise\Test\DispatcherTest\Dispatcher;

final class DispatcherTest extends TestCase {
	public function testConfig() {
		$path = $this->createMock(Path::class);
		$router = $this->createMock(Router::class);
		$response = $this->createMock(Response::class);
		$session = $this->createMock(Session::class);
		$dynamicFactory = $this->createMock(DynamicFactory::class);

		$path->expects($this->any())
			->method('getConfigurationsPath')
			->willReturn(__DIR__ . '/config');

		$dispatcher = new Dispatcher($path, $router, $response, $session, $dynamicFactory);
		$dispatcher->readConfigurations();

		$this->assertSame('App\Handlers', $dispatcher->getHandlerNamespace());
	}

	public function testMatchRoute() {
		$path = $this->createMock(Path::class);
		$router = $this->createMock(Router::class);
		$response = $this->createMock(Response::class);
		$session = $this->createMock(Session::class);
		$dynamicFactory = $this->createMock(DynamicFactory::class);

		$router->expects($this->once())
			->method('match')
			->willReturn(true);

		$session->expects($this->once())
			->method('clearFlash')
			->will($this->returnSelf());

		$dispatcher = new Dispatcher($path, $router, $response, $session, $dynamicFactory);
		$dispatcher->dispatch();
	}

	public function testNotMatchRoute() {
		$path = $this->createMock(Path::class);
		$router = $this->createMock(Router::class);
		$response = $this->createMock(Response::class);
		$session = $this->createMock(Session::class);
		$dynamicFactory = $this->createMock(DynamicFactory::class);

		$router->expects($this->once())
			->method('match')
			->willReturn(false);

		$router->expects($this->once())
			->method('getMatchedStatus')
			->willReturn(404);

		$response->expects($this->once())
			->method('setStatusCode')
			->with($this->equalTo(404))
			->will($this->returnSelf());

		$dispatcher = new Dispatcher($path, $router, $response, $session, $dynamicFactory);
		$dispatcher->dispatch();
	}
}
