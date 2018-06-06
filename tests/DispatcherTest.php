<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use Rise\Test\DispatcherTest\Path;
use Rise\Test\DispatcherTest\Dispatcher;
use Rise\Test\DispatcherTest\Router;
use Rise\Test\DispatcherTest\Response;
use Rise\Test\DispatcherTest\Session;
use Rise\Test\DispatcherTest\DynamicFactory;

final class DispatcherTest extends TestCase {
	public function testConfig() {
		$path = new Path();
		$router = new Router(null, null);
		$response = new Response();
		$session = new Session();
		$dynamicFactory = new DynamicFactory();
		$dispatcher = new Dispatcher($path, $router, $response, $session, $dynamicFactory);
		$dispatcher->readConfigurations();
		$this->assertSame('App\Handlers', $dispatcher->getHandlerNamespace());
	}

	public function testMatchRoute() {
		$path = new Path();
		$router = new Router(true, 200);
		$response = new Response();
		$session = new Session();
		$dynamicFactory = new DynamicFactory();
		$dispatcher = new Dispatcher($path, $router, $response, $session, $dynamicFactory);
		$dispatcher->dispatch();
		$this->assertSame(200, $response->getStatusCode());
		$this->assertTrue($session->getToggled());
	}

	public function testNotMatchRoute() {
		$path = new Path();
		$router = new Router(false, 404);
		$response = new Response();
		$session = new Session();
		$dynamicFactory = new DynamicFactory();
		$dispatcher = new Dispatcher($path, $router, $response, $session, $dynamicFactory);
		$dispatcher->dispatch();
		$this->assertSame(404, $response->getStatusCode());
		$this->assertFalse($session->getToggled());
	}
}
