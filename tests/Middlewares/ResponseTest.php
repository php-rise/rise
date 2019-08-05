<?php
namespace Rise\Test\Middlewares;

use PHPUnit\Framework\TestCase;
use Exception;
use Rise\Middlewares\Response as ResponseMiddleware;
use Rise\Response as ResponseService;
use Rise\Router\RouteNotFoundException;

final class ResponseTest extends TestCase {
	public function testRun() {
		$response = $this->createMock(ResponseService::class);

		$executedNext = false;
		$next = function () use (&$executedNext) {
			$executedNext = true;
		};

		$response->expects($this->once())
			->method('send')
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('end')
			->will($this->returnSelf());

		$middleware = new ResponseMiddleware($response);
		$middleware->run($next);

		$this->assertTrue($executedNext);
	}

	public function testRunHandleRouteNotFound() {
		$response = $this->createMock(ResponseService::class);

		$next = function () use (&$executedNext) {
			throw new RouteNotFoundException;
		};

		$response->expects($this->once())
			->method('setStatusCode')
			->with($this->equalTo(ResponseService::HTTP_NOT_FOUND));

		$response->expects($this->once())
			->method('send')
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('end')
			->will($this->returnSelf());

		$middleware = new ResponseMiddleware($response);
		$middleware->run($next);
	}

	public function testRunHandleException() {
		$response = $this->createMock(ResponseService::class);

		$next = function () use (&$executedNext) {
			throw new Exception;
		};

		$response->expects($this->once())
			->method('setStatusCode')
			->with($this->equalTo(ResponseService::HTTP_INTERNAL_SERVER_ERROR));

		$response->expects($this->once())
			->method('send')
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('end')
			->will($this->returnSelf());

		$middleware = new ResponseMiddleware($response);
		$middleware->run($next);
	}
}
