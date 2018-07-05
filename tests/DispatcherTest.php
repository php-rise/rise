<?php
namespace Rise\Test;

use Closure;
use PHPUnit\Framework\TestCase;
use Rise\Router;
use Rise\Response;
use Rise\Session;
use Rise\Dispatcher\HandlerFactory;
use Rise\Dispatcher;

final class DispatcherTest extends TestCase {
	public function testDispatch() {
		$router = $this->createMock(Router::class);
		$response = $this->createMock(Response::class);
		$handlerFactory = $this->createMock(HandlerFactory::class);
		$sessionMiddleware = $this->getMockBuilder(stdClass::class)
			->setMethods(['setup'])
			->getMock();
		$homeHandler = $this->getMockBuilder(stdClass::class)
			->setMethods(['index'])
			->getMock();
		$sessionMiddlewareSetupNext = ''; // Reference of next middleware
		$homeHandlerIndexNext = ''; // Reference of next middleware

		$router->expects($this->once())
			->method('match')
			->willReturn(true);

		$router->expects($this->once())
			->method('getMatchedStatus')
			->willReturn(200);

		$router->expects($this->once())
			->method('getMatchedHandler')
			->willReturn(['App\Middlewares\Session.setup', 'App\Handlers\Home.index']);

		$handlerFactory->expects($this->exactly(2))
			->method('create')
			->withConsecutive(
				[
					$this->equalTo('App\Middlewares\Session.setup'),
					$this->callback(function ($next) use (&$sessionMiddlewareSetupNext) {
						$sessionMiddlewareSetupNext = $next;
						return $next instanceof Closure;
					})
				],
				[
					$this->equalTo('App\Handlers\Home.index'),
					$this->callback(function ($next) use (&$homeHandlerIndexNext) {
						$homeHandlerIndexNext = $next;
						return $next instanceof Closure;
					})
				]
			)
			->will($this->onConsecutiveCalls(
				[$sessionMiddleware, 'setup', [&$sessionMiddlewareSetupNext]],
				[$homeHandler, 'index', [&$homeHandlerIndexNext]]
			));

		$sessionMiddleware->expects($this->once())
			->method('setup')
			->with($this->callback(function ($next) {
				$next();
				return true;
			}));

		$homeHandler->expects($this->once())
			->method('index')
			->with($this->callback(function ($next) {
				$next();
				return true;
			}));

		$response->expects($this->once())
			->method('setStatusCode')
			->with($this->equalTo(200))
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('send')
			->will($this->returnSelf());

		$dispatcher = new Dispatcher($router, $response, $handlerFactory);
		$dispatcher->dispatch();
	}

	public function testDispatchUnmatchedRoute() {
		$router = $this->createMock(Router::class);
		$response = $this->createMock(Response::class);
		$handlerFactory = $this->createMock(HandlerFactory::class);
		$notFoundHandler = $this->getMockBuilder(stdClass::class)
			->setMethods(['displayErrorPage'])
			->getMock();

		$router->expects($this->once())
			->method('match')
			->willReturn(false);

		$router->expects($this->once())
			->method('getMatchedStatus')
			->willReturn(404);

		$router->expects($this->once())
			->method('getMatchedHandler')
			->willReturn('App\Handlers\NotFoundHandler.displayErrorPage');

		$handlerFactory->expects($this->once())
			->method('create')
			->with($this->equalTo('App\Handlers\NotFoundHandler.displayErrorPage'))
			->willReturn([$notFoundHandler, 'displayErrorPage', []]);

		$response->expects($this->once())
			->method('setStatusCode')
			->with($this->equalTo(404))
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('send')
			->will($this->returnSelf());

		$dispatcher = new Dispatcher($router, $response, $handlerFactory);
		$dispatcher->dispatch();
	}
}
