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

	public function testValidateCsrfForUnmatchedHttpMethod() {
		$sessionService = $this->createMock(SessionService::class);
		$request = $this->createMock(Request::class);
		$response = $this->createMock(Response::class);
		$executedNext = false;

		$next = function () use (&$executedNext) {
			$executedNext = true;
		};

		$request->expects($this->atLeastOnce())
			->method('isMethod')
			->willReturn(false);

		$sessionService->expects($this->never())
			->method('validateCsrfToken');

		$middleware = new SessionMiddleware($sessionService, $request, $response);

		$middleware->validateCsrf($next);

		$this->assertTrue($executedNext);
	}

	public function testValidateCsrfForMatchedHttpMethodAndMatchedCsrfTokenInPostParameter() {
		$sessionService = $this->createMock(SessionService::class);
		$request = $this->createMock(Request::class);
		$response = $this->createMock(Response::class);
		$executedNext = false;

		$next = function () use (&$executedNext) {
			$executedNext = true;
		};

		$sessionService->expects($this->once())
			->method('getCsrfTokenFormKey')
			->willReturn('some_form_key');

		$sessionService->expects($this->once())
			->method('validateCsrfToken')
			->willReturn(true);

		$request->expects($this->atLeastOnce())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->once())
			->method('getInput')
			->with($this->equalTo('some_form_key'))
			->willReturn('some_token');

		$request->expects($this->any())
			->method('getHeader')
			->willReturn(null);

		$middleware = new SessionMiddleware($sessionService, $request, $response);

		$middleware->validateCsrf($next);

		$this->assertTrue($executedNext);
	}

	public function testValidateCsrfForMatchedHttpMethodAndMatchedCsrfTokenInHeader() {
		$sessionService = $this->createMock(SessionService::class);
		$request = $this->createMock(Request::class);
		$response = $this->createMock(Response::class);
		$executedNext = false;

		$next = function () use (&$executedNext) {
			$executedNext = true;
		};

		$sessionService->expects($this->once())
			->method('getCsrfTokenHeaderKey')
			->willReturn('some_header_key');

		$sessionService->expects($this->once())
			->method('validateCsrfToken')
			->willReturn(true);

		$request->expects($this->atLeastOnce())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->any())
			->method('getInput')
			->willReturn(null);

		$request->expects($this->once())
			->method('getHeader')
			->with($this->equalTo('some_header_key'))
			->willReturn('some_token');

		$middleware = new SessionMiddleware($sessionService, $request, $response);

		$middleware->validateCsrf($next);

		$this->assertTrue($executedNext);
	}

	public function testValidateCsrfForMatchedHttpMethodAndUnmatchedCsrfToken() {
		$sessionService = $this->createMock(SessionService::class);
		$request = $this->createMock(Request::class);
		$response = $this->createMock(Response::class);
		$executedNext = false;

		$next = function () use (&$executedNext) {
			$executedNext = true;
		};

		$sessionService->expects($this->once())
			->method('validateCsrfToken')
			->willReturn(false);

		$request->expects($this->atLeastOnce())
			->method('isMethod')
			->willReturn(true);

		$request->expects($this->once())
			->method('getInput')
			->willReturn('some_token');

		$middleware = new SessionMiddleware($sessionService, $request, $response);

		$middleware->validateCsrf($next);

		$this->assertFalse($executedNext);
	}
}
