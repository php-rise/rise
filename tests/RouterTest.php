<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Rise\Router;
use Rise\Router\ScopeFactory;
use Rise\Router\Scope;
use Rise\Router\Result;
use Rise\Path;
use Rise\Request;
use Rise\Locale;

final class RouterTest extends TestCase {
	private $root;

	public function setUp() {
		$routerConfigContent = <<<PHP
<?php
/**
 * Configurations of router.
 *
 * "notFoundHandler": Handler for handling not found route.
 */
return [
	'notFoundHandler' => 'Errors\\NotFound.showHtml',
];
PHP;

		$routesContent = <<<PHP
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
PHP;

		$this->root = vfsStream::setup('root', null, [
			'config' => [
				'router.php' => $routerConfigContent,
				'routes.php' => $routesContent,
			]
		]);
	}

	public function testCreateRootScope() {
		$scopeFactory = $this->createMock(ScopeFactory::class);
		$result = $this->createMock(Result::class);
		$path = $this->createMock(Path::class);
		$request = $this->createMock(Request::class);
		$locale = $this->createMock(Locale::class);
		$scope = $this->createMock(Scope::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$scopeFactory->expects($this->once())
			->method('create')
			->willReturn($scope);

		$router = new Router($scopeFactory, $result, $path, $request, $locale);
		$router->buildRoutes();
	}

	public function testMatch() {
		$scopeFactory = $this->createMock(ScopeFactory::class);
		$result = $this->createMock(Result::class);
		$path = $this->createMock(Path::class);
		$request = $this->createMock(Request::class);
		$locale = $this->createMock(Locale::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$result->expects($this->once())
			->method('hasHandler')
			->willReturn(true);

		$result->expects($this->once())
			->method('getHandler')
			->willReturn(['Product.show']);

		$result->expects($this->once())
			->method('getStatus')
			->willReturn(200);

		$result->expects($this->once())
			->method('getParams')
			->willReturn(['id' => 15]);

		$request->expects($this->once())
			->method('setParams')
			->with($this->equalTo(['id' => 15]));

		$router = new Router($scopeFactory, $result, $path, $request, $locale);
		$matched = $router->match();

		$this->assertTrue($matched);
		$this->assertSame(200, $router->getMatchedStatus());
		$this->assertSame(['Product.show'], $router->getMatchedHandler());
	}

	public function testNotMatch() {
		$scopeFactory = $this->createMock(ScopeFactory::class);
		$result = $this->createMock(Result::class);
		$path = $this->createMock(Path::class);
		$request = $this->createMock(Request::class);
		$locale = $this->createMock(Locale::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$result->expects($this->once())
			->method('hasHandler')
			->willReturn(false);

		$result->expects($this->once())
			->method('getStatus')
			->willReturn(404);

		$router = new Router($scopeFactory, $result, $path, $request, $locale);
		$matched = $router->match();

		$this->assertFalse($matched);
		$this->assertSame(404, $router->getMatchedStatus());
		$this->assertSame('Errors\NotFound.showHtml', $router->getMatchedHandler());
	}
}
