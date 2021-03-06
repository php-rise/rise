<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Rise\Router;
use Rise\Router\ScopeFactory;
use Rise\Router\Scope;
use Rise\Router\Result;
use Rise\Router\RouteNotFoundException;
use Rise\Path;

final class RouterTest extends TestCase {
	private $root;

	public function setUp() {
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
				'routes.php' => $routesContent,
			]
		]);
	}

	public function testCreateRootScope() {
		$scopeFactory = $this->createMock(ScopeFactory::class);
		$result = $this->createMock(Result::class);
		$path = $this->createMock(Path::class);
		$scope = $this->createMock(Scope::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$scopeFactory->expects($this->once())
			->method('create')
			->willReturn($scope);

		$router = new Router($scopeFactory, $result, $path);
		$router->buildRoutes();
	}

	public function testMatch() {
		$scopeFactory = $this->createMock(ScopeFactory::class);
		$result = $this->createMock(Result::class);
		$path = $this->createMock(Path::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$result->expects($this->once())
			->method('hasHandler')
			->willReturn(true);

		$result->expects($this->once())
			->method('getHandler')
			->willReturn(['Product.show']);

		$router = new Router($scopeFactory, $result, $path);
		$matchedHandlers = $router->match();

		$this->assertSame(['Product.show'], $matchedHandlers);
	}

	public function testNotMatch() {
		$this->expectException(RouteNotFoundException::class);

		$scopeFactory = $this->createMock(ScopeFactory::class);
		$result = $this->createMock(Result::class);
		$path = $this->createMock(Path::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$result->expects($this->once())
			->method('hasHandler')
			->willReturn(false);

		$router = new Router($scopeFactory, $result, $path);
		$router->match();
	}
}
