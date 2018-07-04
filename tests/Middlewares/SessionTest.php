<?php
namespace Rise\Test\Middlewares;

use PHPUnit\Framework\TestCase;
use Rise\Middlewares\Session as SessionMiddleware;
use Rise\Session as SessionService;
use Rise\Http\Request;
use Rise\Http\Response;

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

	public function testValidateCsrfForMatchedHttpMethodAndMatchedCsrfToken() {
		$sessionService = $this->createMock(SessionService::class);
		$request = $this->createMock(Request::class);
		$response = $this->createMock(Response::class);
		$executedNext = false;

		$next = function () use (&$executedNext) {
			$executedNext = true;
		};

		$request->expects($this->atLeastOnce())
			->method('isMethod')
			->willReturn(true);

		$sessionService->expects($this->once())
			->method('validateCsrfToken')
			->willReturn(true);

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

		$request->expects($this->atLeastOnce())
			->method('isMethod')
			->willReturn(true);

		$sessionService->expects($this->once())
			->method('validateCsrfToken')
			->willReturn(false);

		$middleware = new SessionMiddleware($sessionService, $request, $response);

		$middleware->validateCsrf($next);

		$this->assertFalse($executedNext);
	}
}
