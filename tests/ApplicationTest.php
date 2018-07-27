<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use Rise\Application;
use Rise\Path;
use Rise\Container;
use Rise\Router;
use Rise\Dispatcher;
use Rise\Response;

final class ApplicationTest extends TestCase {
	public function setProjectRoot() {
		$path = $this->createMock(Path::class);
		$container = $this->createMock(Container::class);

		$path->expects($this->once())
			->method('setProjectRootPath');

		$application = new Application($container, $path);
		$application->setProjectRoot('some/path');
	}

	public function testRun() {
		$path = $this->createMock(Path::class);
		$container = $this->createMock(Container::class);
		$router = $this->createMock(Router::class);
		$dispatcher = $this->createMock(Dispatcher::class);
		$response = $this->createMock(Response::class);

		$router->expects($this->once())
			->method('buildRoutes')
			->will($this->returnSelf());

		$router->expects($this->once())
			->method('match')
			->will($this->returnSelf());

		$router->expects($this->once())
			->method('getMatchedHandler')
			->willReturn(['Handler.handle']);

		$router->expects($this->once())
			->method('getMatchedStatus')
			->willReturn(200);

		$dispatcher->expects($this->once())
			->method('setHandlers')
			->with($this->equalTo(['Handler.handle']))
			->will($this->returnSelf());

		$dispatcher->expects($this->once())
			->method('dispatch')
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('setStatusCode')
			->with($this->equalTo(200))
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('send')
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('end')
			->will($this->returnSelf());

		$container->expects($this->exactly(3))
			->method('get')
			->will($this->returnCallback(function ($class) use ($router, $dispatcher, $response) {
				switch ($class) {
				case Router::class:
					return $router;
				case Dispatcher::class:
					return $dispatcher;
				case Response::class:
					return $response;
				default:
					return null;
				}
			}));

		$application = new Application($container, $path);
		$application->run();
	}
}
