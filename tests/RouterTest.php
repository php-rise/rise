<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use Rise\Test\RouterTest\Router;
use Rise\Test\RouterTest\RoutingEngine;
use Rise\Test\RouterTest\ScopeFactory;
use Rise\Test\RouterTest\Path;
use Rise\Test\RouterTest\Request;
use Rise\Test\RouterTest\Locale;

final class RouterTest extends TestCase {
	public function setUp() {
		$_SERVER['HTTP_HOST'] = 'www.example.com';
	}

	public function testConfig() {
		$routingEngine = new RoutingEngine();
		$scopeFactory = new ScopeFactory();
		$path = new Path();
		$request = new Request();
		$locale = new Locale();
		$router = new Router($routingEngine, $scopeFactory, $path, $request, $locale);

		$router->readConfigurations();

		$this->assertSame(__DIR__ . '/config/routes.php', $router->getRoutesFile());
	}

	public function testMatchRoot() {
		$routingEngine = new RoutingEngine();
		$scopeFactory = new ScopeFactory();
		$path = new Path();
		$request = new Request('/');
		$locale = new Locale();
		$router = new Router($routingEngine, $scopeFactory, $path, $request, $locale);

		$router->readConfigurations();
		$router->buildRoutes();
		$matched = $router->match();

		$this->assertTrue($matched);
		$this->assertSame(200, $router->getMatchedStatus());
		$this->assertSame(['Home.index'], $router->getMatchedHandler());
	}

	public function testMatchPath() {
		$routingEngine = new RoutingEngine();
		$scopeFactory = new ScopeFactory();
		$path = new Path();
		$request = new Request('/contact');
		$locale = new Locale();
		$router = new Router($routingEngine, $scopeFactory, $path, $request, $locale);

		$router->readConfigurations();
		$router->buildRoutes();
		$matched = $router->match();

		$this->assertTrue($matched);
		$this->assertSame(200, $router->getMatchedStatus());
		$this->assertSame(['Contact.index'], $router->getMatchedHandler());
	}

	public function testMatchPathWithParams() {
		$routingEngine = new RoutingEngine();
		$scopeFactory = new ScopeFactory();
		$path = new Path();
		$request = new Request('/products/15');
		$locale = new Locale();
		$router = new Router($routingEngine, $scopeFactory, $path, $request, $locale);

		$router->readConfigurations();
		$router->buildRoutes();
		$matched = $router->match();

		$this->assertTrue($matched);
		$this->assertSame(200, $router->getMatchedStatus());
		$this->assertSame(['Product.show'], $router->getMatchedHandler());
		$this->assertSame('15', $request->getParam('id'));
	}

	public function testNotMatch() {
		$routingEngine = new RoutingEngine();
		$scopeFactory = new ScopeFactory();
		$path = new Path();
		$request = new Request('/not/found');
		$locale = new Locale();
		$router = new Router($routingEngine, $scopeFactory, $path, $request, $locale);

		$router->readConfigurations();
		$router->buildRoutes();
		$matched = $router->match();

		$this->assertFalse($matched);
		$this->assertSame(404, $router->getMatchedStatus());
		$this->assertSame('Errors\NotFound.html', $router->getMatchedHandler());
	}

	public function testGenerateUrl() {
		$routingEngine = new RoutingEngine();
		$scopeFactory = new ScopeFactory();
		$path = new Path();
		$request = new Request();
		$locale = new Locale();
		$router = new Router($routingEngine, $scopeFactory, $path, $request, $locale);

		$router->readConfigurations();
		$router->buildRoutes();

		$this->assertSame('http://www.example.com/', $router->generateUrl('root'));
		$this->assertSame('http://www.example.com/contact', $router->generateUrl('contact'));
		$this->assertSame('http://www.example.com/products/15', $router->generateUrl('productDetail', ['id' => 15]));
	}

	public function testGeneratePath() {
		$routingEngine = new RoutingEngine();
		$scopeFactory = new ScopeFactory();
		$path = new Path();
		$request = new Request();
		$locale = new Locale();
		$router = new Router($routingEngine, $scopeFactory, $path, $request, $locale);

		$router->readConfigurations();
		$router->buildRoutes();

		$this->assertSame('/', $router->generatePath('root'));
		$this->assertSame('/contact', $router->generatePath('contact'));
		$this->assertSame('/products/16', $router->generatePath('productDetail', ['id' => 16]));
	}
}
