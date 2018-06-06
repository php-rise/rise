<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use Rise\Router\RoutingEngine;
use Rise\Router\ScopeFactory;
use Rise\Router\Scope;
use Rise\Path;
use Rise\Http\Request;
use Rise\Locale;
use Rise\Test\RouterTest\Router;

final class RouterTest extends TestCase {
	public function setUp() {
		$_SERVER['HTTP_HOST'] = 'www.example.com';
	}

	public function testConfig() {
		$routingEngine = $this->createMock(RoutingEngine::class);
		$scopeFactory = $this->createMock(ScopeFactory::class);
		$path = $this->createMock(Path::class);
		$request = $this->createMock(Request::class);
		$locale = $this->createMock(Locale::class);

		$path->expects($this->any())
			->method('getProjectRootPath')
			->willReturn(__DIR__);

		$path->expects($this->any())
			->method('getConfigurationsPath')
			->willReturn(__DIR__ . '/config');

		$router = new Router($routingEngine, $scopeFactory, $path, $request, $locale);
		$router->readConfigurations();

		$this->assertSame(__DIR__ . '/config/routes.php', $router->getRoutesFile());
		$this->assertSame('Errors\NotFound.html', $router->getNotFoundHandler());
	}

	public function testMatch() {
		$routingEngine = $this->createMock(RoutingEngine::class);
		$scopeFactory = $this->createMock(ScopeFactory::class);
		$path = $this->createMock(Path::class);
		$request = $this->createMock(Request::class);
		$locale = $this->createMock(Locale::class);

		$routingEngine->expects($this->once())
			->method('dispatch')
			->willReturn([
				'handler' => 'Product.show',
				'params' => [
					'id' => 15,
				],
			]);

		$request->expects($this->once())
			->method('setParams')
			->with($this->equalTo(['id' => 15]));

		$router = new Router($routingEngine, $scopeFactory, $path, $request, $locale);
		$matched = $router->match();

		$this->assertTrue($matched);
		$this->assertSame(200, $router->getMatchedStatus());
		$this->assertSame('Product.show', $router->getMatchedHandler());
	}

	public function testNotMatch() {
		$routingEngine = $this->createMock(RoutingEngine::class);
		$scopeFactory = $this->createMock(ScopeFactory::class);
		$path = $this->createMock(Path::class);
		$request = $this->createMock(Request::class);
		$locale = $this->createMock(Locale::class);

		$routingEngine->expects($this->once())
			->method('dispatch')
			->willReturn([
				'error' => [
					'code' => 404,
				],
			]);

		$path->expects($this->any())
			->method('getProjectRootPath')
			->willReturn(__DIR__);

		$path->expects($this->any())
			->method('getConfigurationsPath')
			->willReturn(__DIR__ . '/config');

		$router = new Router($routingEngine, $scopeFactory, $path, $request, $locale);
		$router->readConfigurations();
		$matched = $router->match();

		$this->assertFalse($matched);
		$this->assertSame(404, $router->getMatchedStatus());
		$this->assertSame('Errors\NotFound.html', $router->getMatchedHandler());
	}

	public function testGeneratePathWithLocale() {
		$routingEngine = $this->createMock(RoutingEngine::class);
		$scopeFactory = $this->createMock(ScopeFactory::class);
		$path = $this->createMock(Path::class);
		$request = $this->createMock(Request::class);
		$locale = $this->createMock(Locale::class);

		$routingEngine->expects($this->once())
			->method('generatePath')
			->willReturn('/contact');

		$locale->expects($this->once())
			->method('getCurrentLocaleCode')
			->willReturn('en');

		$router = new Router($routingEngine, $scopeFactory, $path, $request, $locale);

		$this->assertSame('/en/contact', $router->generatePath(''));
	}

	public function testGeneratePathWithoutLocale() {
		$routingEngine = $this->createMock(RoutingEngine::class);
		$scopeFactory = $this->createMock(ScopeFactory::class);
		$path = $this->createMock(Path::class);
		$request = $this->createMock(Request::class);
		$locale = $this->createMock(Locale::class);

		$routingEngine->expects($this->once())
			->method('generatePath')
			->willReturn('/contact');

		$locale->expects($this->once())
			->method('getCurrentLocaleCode')
			->willReturn('');

		$router = new Router($routingEngine, $scopeFactory, $path, $request, $locale);

		$this->assertSame('/contact', $router->generatePath(''));
	}
}
