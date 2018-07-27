<?php
namespace Rise\Test\Middlewares;

use PHPUnit\Framework\TestCase;
use Rise\Middlewares\Session as SessionMiddleware;
use Rise\Session as SessionService;
use Rise\Request;
use Rise\Response;

final class SessionTest extends TestCase {
	public function testRun() {
		$sessionService = $this->createMock(SessionService::class);
		$request = $this->createMock(Request::class);
		$response = $this->createMock(Response::class);
		$executedNext = false;
		$next = function () use (&$executedNext) {
			$executedNext = true;
		};

		$sessionService->expects($this->once())
			->method('start');

		$sessionService->expects($this->once())
			->method('toNextFlash');

		$middleware = new SessionMiddleware($sessionService, $request, $response);

		$middleware->run($next);

		$this->assertTrue($executedNext);
	}

	public function testValidateCsrfIgnoreHttpGetRequest() {
		$middleware = $this->getSessionMiddlewareForValidateCsrfIgnoreHttpMethod('GET');
		$executedNext = false;
		$next = function () use (&$executedNext) {
			$executedNext = true;
		};
		$middleware->validateCsrf($next);

		$this->assertTrue($executedNext);
	}

	public function testValidateCsrfIgnoreHttpHeadRequest() {
		$middleware = $this->getSessionMiddlewareForValidateCsrfIgnoreHttpMethod('HEAD');
		$executedNext = false;
		$next = function () use (&$executedNext) {
			$executedNext = true;
		};
		$middleware->validateCsrf($next);

		$this->assertTrue($executedNext);
	}

	public function testValidateCsrfIgnoreHttpOptionsRequest() {
		$middleware = $this->getSessionMiddlewareForValidateCsrfIgnoreHttpMethod('OPTIONS');
		$executedNext = false;
		$next = function () use (&$executedNext) {
			$executedNext = true;
		};
		$middleware->validateCsrf($next);

		$this->assertTrue($executedNext);
	}

	public function testValidateCsrfIgnoreHttpTraceRequest() {
		$middleware = $this->getSessionMiddlewareForValidateCsrfIgnoreHttpMethod('TRACE');
		$executedNext = false;
		$next = function () use (&$executedNext) {
			$executedNext = true;
		};
		$middleware->validateCsrf($next);

		$this->assertTrue($executedNext);
	}

	public function testValdiateCsrfIgnoreHttpConnectRequest() {
		$middleware = $this->getSessionMiddlewareForValidateCsrfIgnoreHttpMethod('CONNECT');
		$executedNext = false;
		$next = function () use (&$executedNext) {
			$executedNext = true;
		};
		$middleware->validateCsrf($next);

		$this->assertTrue($executedNext);
	}

	public function testValidateCsrfCheckHttpPostRequestAndPostParametersWithMatchedCsrfToken() {
		$sessionService = $this->createMock(SessionService::class);
		$request = $this->createMock(Request::class);
		$response = $this->createMock(Response::class);

		$sessionService->expects($this->once())
			->method('getCsrfTokenFormKey')
			->willReturn('csrf_form_key');

		$sessionService->expects($this->once())
			->method('validateCsrfToken')
			->willReturn(true);

		$request->expects($this->atLeastOnce())
			->method('getMethod')
			->willReturn('POST');

		$request->expects($this->atLeastOnce())
			->method('getInput')
			->willReturn(['csrf_form_key' => 'secret_csrf_token']);

		$middleware = new SessionMiddleware($sessionService, $request, $response);
		$executedNext = false;
		$next = function () use (&$executedNext) {
			$executedNext = true;
		};
		$middleware->validateCsrf($next);

		$this->assertTrue($executedNext);
	}

	public function testValidateCsrfCheckHttpPostRequestAndPostParametersWithWrongCsrfToken() {
		$sessionService = $this->createMock(SessionService::class);
		$request = $this->createMock(Request::class);
		$response = $this->createMock(Response::class);

		$sessionService->expects($this->once())
			->method('getCsrfTokenFormKey')
			->willReturn('csrf_form_key');

		$sessionService->expects($this->once())
			->method('validateCsrfToken')
			->willReturn(false);

		$request->expects($this->atLeastOnce())
			->method('getMethod')
			->willReturn('POST');

		$request->expects($this->atLeastOnce())
			->method('getInput')
			->willReturn(['csrf_form_key' => 'wrong_csrf_token']);

		$middleware = new SessionMiddleware($sessionService, $request, $response);
		$executedNext = false;
		$next = function () use (&$executedNext) {
			$executedNext = true;
		};
		$middleware->validateCsrf($next);

		$this->assertFalse($executedNext);
	}

	public function testValidateCsrfCheckHttpPutRequestAndPutParametersWithMatchedCsrfToken() {
		$sessionService = $this->createMock(SessionService::class);
		$request = $this->createMock(Request::class);
		$response = $this->createMock(Response::class);

		$sessionService->expects($this->once())
			->method('getCsrfTokenFormKey')
			->willReturn('csrf_form_key');

		$sessionService->expects($this->once())
			->method('validateCsrfToken')
			->willReturn(true);

		$request->expects($this->atLeastOnce())
			->method('getMethod')
			->willReturn('PUT');

		$request->expects($this->atLeastOnce())
			->method('getInput')
			->willReturn(['csrf_form_key' => 'secret_csrf_token']);

		$middleware = new SessionMiddleware($sessionService, $request, $response);
		$executedNext = false;
		$next = function () use (&$executedNext) {
			$executedNext = true;
		};
		$middleware->validateCsrf($next);

		$this->assertTrue($executedNext);
	}

	public function testValidateCsrfCheckHttpPutRequestAndPutParametersWithWrongCsrfToken() {
		$sessionService = $this->createMock(SessionService::class);
		$request = $this->createMock(Request::class);
		$response = $this->createMock(Response::class);

		$sessionService->expects($this->once())
			->method('getCsrfTokenFormKey')
			->willReturn('csrf_form_key');

		$sessionService->expects($this->once())
			->method('validateCsrfToken')
			->willReturn(false);

		$request->expects($this->atLeastOnce())
			->method('getMethod')
			->willReturn('PUT');

		$request->expects($this->atLeastOnce())
			->method('getInput')
			->willReturn(['csrf_form_key' => 'wrong_csrf_token']);

		$middleware = new SessionMiddleware($sessionService, $request, $response);
		$executedNext = false;
		$next = function () use (&$executedNext) {
			$executedNext = true;
		};
		$middleware->validateCsrf($next);

		$this->assertFalse($executedNext);
	}

	public function testValidateCsrfForMatchedHttpMethodAndMatchedCsrfTokenInHeader() {
		$sessionService = $this->createMock(SessionService::class);
		$request = $this->createMock(Request::class);
		$response = $this->createMock(Response::class);

		$sessionService->expects($this->once())
			->method('getCsrfTokenHeaderKey')
			->willReturn('csrf_header_key');

		$sessionService->expects($this->once())
			->method('validateCsrfToken')
			->willReturn(true);

		$request->expects($this->atLeastOnce())
			->method('getMethod')
			->willReturn('POST');

		$request->expects($this->once())
			->method('getHeader')
			->with($this->equalTo('csrf_header_key'))
			->willReturn('secret_csrf_token');

		$middleware = new SessionMiddleware($sessionService, $request, $response);
		$executedNext = false;
		$next = function () use (&$executedNext) {
			$executedNext = true;
		};
		$middleware->validateCsrf($next);

		$this->assertTrue($executedNext);
	}

	public function testValidateCsrfUnmatchedCsrfTokenInHeader() {
		$sessionService = $this->createMock(SessionService::class);
		$request = $this->createMock(Request::class);
		$response = $this->createMock(Response::class);

		$sessionService->expects($this->once())
			->method('getCsrfTokenHeaderKey')
			->willReturn('csrf_header_key');

		$sessionService->expects($this->once())
			->method('validateCsrfToken')
			->willReturn(false);

		$request->expects($this->atLeastOnce())
			->method('getMethod')
			->willReturn('POST');

		$request->expects($this->once())
			->method('getHeader')
			->with($this->equalTo('csrf_header_key'))
			->willReturn('secret_csrf_token');

		$middleware = new SessionMiddleware($sessionService, $request, $response);
		$executedNext = false;
		$next = function () use (&$executedNext) {
			$executedNext = true;
		};
		$middleware->validateCsrf($next);

		$this->assertFalse($executedNext);
	}

	private function getSessionMiddlewareForValidateCsrfIgnoreHttpMethod($httpMethod) {
		$sessionService = $this->createMock(SessionService::class);
		$request = $this->createMock(Request::class);
		$response = $this->createMock(Response::class);

		$request->expects($this->atLeastOnce())
			->method('getMethod')
			->willReturn($httpMethod);

		$sessionService->expects($this->never())
			->method('validateCsrfToken');

		return new SessionMiddleware($sessionService, $request, $response);
	}
}
