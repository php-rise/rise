<?php
namespace Rise\Test\Router;

use Exception;
use PHPUnit\Framework\TestCase;
use Rise\Router\Scope;
use Rise\Request;
use Rise\Router\Result;
use Rise\Router\UrlGenerator;

final class ScopeTest extends TestCase {
	public function testMatchStaticRoute() {
		$request = $this->createMock(Request::class);
		$result = $this->createMock(Result::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$request->expects($this->once())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->once())
			->method('getPath')
			->willReturn('/products');

		$result->expects($this->once())
			->method('setHandler')
			->with($this->equalTo(['Handler.handle']));

		$scope = new Scope($request, $result, $urlGenerator);

		$scope->on('GET', '/products', 'Handler.handle');
	}

	public function testMatchDynamicRoute() {
		$request = $this->createMock(Request::class);
		$result = $this->createMock(Result::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$request->expects($this->once())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->once())
			->method('getPath')
			->willReturn('/products/15');

		$result->expects($this->once())
			->method('setHandler')
			->with($this->equalTo(['Handler.handle']));

		$result->expects($this->once())
			->method('setParams')
			->with($this->equalTo(['id' => '15']));

		$scope = new Scope($request, $result, $urlGenerator);

		$scope->on('GET', '/products/{id}', 'Handler.handle');
	}

	public function testMatchOnlyFirstRoute() {
		$request = $this->createMock(Request::class);
		$result = $this->createMock(Result::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$request->expects($this->any())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->once())
			->method('getPath')
			->willReturn('/products/15');

		$result->expects($this->exactly(2))
			->method('hasHandler')
			->will($this->onConsecutiveCalls(false, true));

		$result->expects($this->once())
			->method('setHandler')
			->with($this->equalTo(['Product.show']));

		$scope = new Scope($request, $result, $urlGenerator);

		$scope->on('GET', '/products/{id}', 'Product.show');
		$scope->on('GET', '/products/{pid}', 'Product.showp');
	}

	public function testNamespace() {
		$request = $this->createMock(Request::class);
		$result = $this->createMock(Result::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$request->expects($this->once())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->once())
			->method('getPath')
			->willReturn('/products');

		$result->expects($this->once())
			->method('setHandler')
			->with($this->equalTo(['App\Handlers\Handler.handle']));

		$scope = new Scope($request, $result, $urlGenerator);

		$scope->namespace('App\Handlers');
		$scope->on('GET', '/products', 'Handler.handle');
	}

	public function testNamespaceInChildScope() {
		$request = $this->createMock(Request::class);
		$result = $this->createMock(Result::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$request->expects($this->once())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->once())
			->method('getPath')
			->willReturn('/products');

		$result->expects($this->once())
			->method('setHandler')
			->with($this->equalTo(['App\Handlers\Handler.handle']));

		$scope = new Scope($request, $result, $urlGenerator);

		$scope->namespace('App\Handlers');
		$scope->createScope(function ($scope) {
			$scope->on('GET', '/products', 'Handler.handle');
		});
	}

	public function testChangeNamespaceInChildScope() {
		$request = $this->createMock(Request::class);
		$result = $this->createMock(Result::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$request->expects($this->once())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->once())
			->method('getPath')
			->willReturn('/products');

		$result->expects($this->once())
			->method('setHandler')
			->with($this->equalTo(['App\Controllers\Handler.handle']));

		$scope = new Scope($request, $result, $urlGenerator);

		$scope->namespace('App\Handlers');
		$scope->createScope(function ($scope) {
			$scope->namespace('App\Controllers');
			$scope->on('GET', '/products', 'Handler.handle');
		});
	}

	public function testResetNamespaceInChildScope() {
		$request = $this->createMock(Request::class);
		$result = $this->createMock(Result::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$request->expects($this->once())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->once())
			->method('getPath')
			->willReturn('/products');

		$result->expects($this->once())
			->method('setHandler')
			->with($this->equalTo(['Handler.handle']));

		$scope = new Scope($request, $result, $urlGenerator);

		$scope->namespace('App\Handlers');
		$scope->createScope(function ($scope) {
			$scope->namespace('');
			$scope->on('GET', '/products', 'Handler.handle');
		});
	}

	public function testMiddlewares() {
		$request = $this->createMock(Request::class);
		$result = $this->createMock(Result::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$request->expects($this->once())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->once())
			->method('getPath')
			->willReturn('/products');

		$result->expects($this->once())
			->method('setHandler')
			->with($this->equalTo(['Middleware.run', 'Handler.handle']));

		$scope = new Scope($request, $result, $urlGenerator);

		$scope->use(['Middleware.run']);
		$scope->on('GET', '/products', 'Handler.handle');
	}

	public function testNamespacedMiddlewares() {
		$request = $this->createMock(Request::class);
		$result = $this->createMock(Result::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$request->expects($this->once())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->once())
			->method('getPath')
			->willReturn('/products');

		$result->expects($this->once())
			->method('setHandler')
			->with($this->equalTo(['App\Handlers\Middleware.run', 'App\Handlers\Handler.handle']));

		$scope = new Scope($request, $result, $urlGenerator);

		$scope->namespace('App\Handlers');
		$scope->use(['Middleware.run']);
		$scope->on('GET', '/products', 'Handler.handle');
	}

	public function testMiddlewaresInChildScope() {
		$request = $this->createMock(Request::class);
		$result = $this->createMock(Result::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$request->expects($this->once())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->once())
			->method('getPath')
			->willReturn('/products');

		$result->expects($this->once())
			->method('setHandler')
			->with($this->equalTo(['Middleware.run', 'Handler.handle']));

		$scope = new Scope($request, $result, $urlGenerator);

		$scope->use(['Middleware.run']);
		$scope->createScope(function ($scope) {
			$scope->on('GET', '/products', 'Handler.handle');
		});
	}

	public function testMatchPrefix() {
		$request = $this->createMock(Request::class);
		$result = $this->createMock(Result::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$request->expects($this->any())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->exactly(2))
			->method('getPath')
			->willReturn('/products/1/comments/2');

		$result->expects($this->once())
			->method('setHandler')
			->with($this->equalTo(['Handler.handle']));

		$result->expects($this->once())
			->method('setParams')
			->with($this->equalTo(['pid' => '1', 'cid' => '2']));

		$scope = new Scope($request, $result, $urlGenerator);

		$scope->prefix('/products/{pid}');
		$scope->on('GET', '/comments/{cid}', 'Handler.handle');
	}

	public function testMatchPrefixWithChildScope() {
		$request = $this->createMock(Request::class);
		$result = $this->createMock(Result::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$request->expects($this->any())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->exactly(2))
			->method('getPath')
			->willReturn('/products/1/comments/2');

		$result->expects($this->once())
			->method('setHandler')
			->with($this->equalTo(['Handler.handle']));

		$result->expects($this->once())
			->method('setParams')
			->with($this->equalTo(['pid' => '1', 'cid' => '2']));

		$scope = new Scope($request, $result, $urlGenerator);

		$scope->prefix('/products/{pid}');
		$scope->createScope(function ($scope) {
			$scope->on('GET', '/comments/{cid}', 'Handler.handle');
		});
	}

	public function testCallPrefixAgainInSameScope() {
		$request = $this->createMock(Request::class);
		$result = $this->createMock(Result::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$request->expects($this->any())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->atLeastOnce())
			->method('getPath')
			->willReturn('/products/1');

		$result->expects($this->once())
			->method('setHandler')
			->with($this->equalTo(['ProductHandler.handle']));

		$result->expects($this->once())
			->method('setParams')
			->with($this->equalTo(['pid' => '1']));

		$scope = new Scope($request, $result, $urlGenerator);

		$scope->prefix('/blogs');
		$scope->on('GET', '/{bid}', 'BlogHandler.handle');
		$scope->prefix('/products');
		$scope->on('GET', '/{pid}', 'ProductHandler.handle');
	}

	public function testNotMatchRoute() {
		$request = $this->createMock(Request::class);
		$result = $this->createMock(Result::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$request->expects($this->once())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->once())
			->method('getPath')
			->willReturn('/products');

		$result->expects($this->never())
			->method('setHandler');

		$scope = new Scope($request, $result, $urlGenerator);

		$scope->on('GET', '/product', 'Handler.handle');
	}
	public function testNotMatchHttpMethod() {
		$request = $this->createMock(Request::class);
		$result = $this->createMock(Result::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$request->expects($this->once())
			->method('isMethod')
			->willReturn(false);

		$request->expects($this->never())
			->method('getPath');

		$result->expects($this->never())
			->method('setHandler');

		$scope = new Scope($request, $result, $urlGenerator);

		$scope->on('GET', '/products', 'Handler.handle');
	}

	public function testNotMatchPrefix() {
		$request = $this->createMock(Request::class);
		$result = $this->createMock(Result::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$request->expects($this->any())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->once())
			->method('getPath')
			->willReturn('/products/1/comments/2');

		$result->expects($this->never())
			->method('setHandler');

		$result->expects($this->never())
			->method('setParams');

		$scope = new Scope($request, $result, $urlGenerator);

		$scope->prefix('/product/{pid}');
		$scope->on('GET', '/comments/{cid}', 'Handler.handle');
	}

	public function testNotMatchPrefixWithChildScope() {
		$request = $this->createMock(Request::class);
		$result = $this->createMock(Result::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$request->expects($this->any())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->once())
			->method('getPath')
			->willReturn('/products/1/comments/2');

		$result->expects($this->never())
			->method('setHandler');

		$result->expects($this->never())
			->method('setParams');

		$scope = new Scope($request, $result, $urlGenerator);

		$scope->prefix('/product/{pid}');
		$scope->createScope(function ($scope) {
			$scope->on('GET', '/comments/{cid}', 'Handler.handle');
		});
	}

	public function testNamedRoute() {
		$request = $this->createMock(Request::class);
		$result = $this->createMock(Result::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$request->expects($this->once())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->once())
			->method('getPath')
			->willReturn('/products');

		$urlGenerator->expects($this->once())
			->method('add')
			->with(
				$this->equalTo('products'),
				$this->equalTo('/products')
			);

		$scope = new Scope($request, $result, $urlGenerator);

		$scope->on('GET', '/products', 'Handler.handle', 'products');
	}

	public function testNamedRouteWithPrefix() {
		$request = $this->createMock(Request::class);
		$result = $this->createMock(Result::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$request->expects($this->any())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->atLeastOnce())
			->method('getPath')
			->willReturn('/products/1/comments/2');

		$urlGenerator->expects($this->once())
			->method('add')
			->with(
				$this->equalTo('route.name'),
				$this->equalTo('/products/{pid}/comments/{cid}')
			);

		$scope = new Scope($request, $result, $urlGenerator);

		$scope->prefix('/products/{pid}');
		$scope->on('GET', '/comments/{cid}', 'Handler.handle', 'route.name');
	}

	public function testUnmatchedNamedRoute() {
		$request = $this->createMock(Request::class);
		$result = $this->createMock(Result::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$request->expects($this->once())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->once())
			->method('getPath')
			->willReturn('/products');

		$urlGenerator->expects($this->once())
			->method('add')
			->with(
				$this->equalTo('unmatched.route.name'),
				$this->equalTo('/product')
			);

		$scope = new Scope($request, $result, $urlGenerator);

		$scope->on('GET', '/product', 'Handler.handle', 'unmatched.route.name');
	}
}
