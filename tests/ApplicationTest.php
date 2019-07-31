<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use Rise\Application;
use Rise\Path;
use Rise\Container;
use Rise\Dispatcher;

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
		$dispatcher = $this->createMock(Dispatcher::class);

		// Default handlers
		$dispatcher->expects($this->once())
			->method('setHandlers')
			->with($this->equalTo([
				'Rise\Middlewares\Response.run',
				'Rise\Middlewares\Router.run',
			]))
			->will($this->returnSelf());

		$dispatcher->expects($this->once())
			->method('readConfig')
			->will($this->returnSelf());

		$dispatcher->expects($this->once())
			->method('dispatch')
			->will($this->returnSelf());

		$container->expects($this->once())
			->method('get')
			->will($this->returnCallback(function ($class) use ($dispatcher) {
				switch ($class) {
				case Dispatcher::class:
					return $dispatcher;
				default:
					return null;
				}
			}));

		$application = new Application($container, $path);
		$application->run();
	}
}
