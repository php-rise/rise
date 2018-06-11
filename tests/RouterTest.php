<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Rise\Router;
use Rise\Router\RoutingEngine;
use Rise\Router\ScopeFactory;
use Rise\Router\Scope;
use Rise\Path;
use Rise\Http\Request;
use Rise\Locale;

final class RouterTest extends TestCase {
	private $root;

	public function setUp() {
		$_SERVER['HTTP_HOST'] = 'www.example.com';

		$routerConfigContent = <<<EOD
<?php
/**
 * Configurations of router.
 *
 * "routesFile": Location of the routes file relative to the project root.
 *
 * "notFoundHandler": Handler for handling not found route.
 */
return [
	'routesFile' => 'config/routes.php',
	'notFoundHandler' => 'Errors\\NotFound.showHtml',
];
EOD;

		$routesContent = <<<EOD
<?php
/**
 * @var \\Rise\\Router\\Scope \$scope
 */
\$scope->get('', 'Home.index', 'root');
\$scope->get('contact', 'Contact.index', 'contact');
\$scope->createScope(function(\$scope) {
	\$scope->setPrefix('products');
	\$scope->get('{id}', 'Product.show', 'productDetail');
});
EOD;

		$this->root = vfsStream::setup('root', null, [
			'config' => [
				'router.php' => $routerConfigContent,
				'routes.php' => $routesContent,
			]
		]);
	}

	public function tearDown() {
		unset($_SERVER['HTTP_HOST']);
	}

	public function testMatch() {
		$routingEngine = $this->createMock(RoutingEngine::class);
		$scopeFactory = $this->createMock(ScopeFactory::class);
		$path = $this->createMock(Path::class);
		$request = $this->createMock(Request::class);
		$locale = $this->createMock(Locale::class);

		$path->expects($this->any())
			->method('getProjectRootPath')
			->willReturn(vfsStream::url('root'));

		$path->expects($this->any())
			->method('getConfigurationsPath')
			->willReturn(vfsStream::url('root/config'));

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

		$path->expects($this->any())
			->method('getProjectRootPath')
			->willReturn(vfsStream::url('root'));

		$path->expects($this->any())
			->method('getConfigurationsPath')
			->willReturn(vfsStream::url('root/config'));

		$routingEngine->expects($this->once())
			->method('dispatch')
			->willReturn([
				'error' => [
					'code' => 404,
				],
			]);

		$router = new Router($routingEngine, $scopeFactory, $path, $request, $locale);
		$matched = $router->match();

		$this->assertFalse($matched);
		$this->assertSame(404, $router->getMatchedStatus());
		$this->assertSame('Errors\NotFound.showHtml', $router->getMatchedHandler());
	}

	public function testGeneratePathWithLocale() {
		$routingEngine = $this->createMock(RoutingEngine::class);
		$scopeFactory = $this->createMock(ScopeFactory::class);
		$path = $this->createMock(Path::class);
		$request = $this->createMock(Request::class);
		$locale = $this->createMock(Locale::class);

		$path->expects($this->any())
			->method('getProjectRootPath')
			->willReturn(vfsStream::url('root'));

		$path->expects($this->any())
			->method('getConfigurationsPath')
			->willReturn(vfsStream::url('root/config'));

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

		$path->expects($this->any())
			->method('getProjectRootPath')
			->willReturn(vfsStream::url('root'));

		$path->expects($this->any())
			->method('getConfigurationsPath')
			->willReturn(vfsStream::url('root/config'));

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
