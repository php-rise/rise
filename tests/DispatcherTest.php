<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Rise\Path;
use Rise\Router;
use Rise\Http\Response;
use Rise\Session;
use Rise\Dispatcher\HandlerFactory;
use Rise\Dispatcher;

final class DispatcherTest extends TestCase {
	private $root;

	public function setUp() {
		$dispatcherConfigContent = <<<EOD
<?php
/**
 * Configurations of dispatcher.
 *
 * "handlerNamespace": Namespace of handlers.
 */
return [
	'handlerNamespace' => 'App\Handlers',
];
EOD;
		$this->root = vfsStream::setup('root', null, [
			'config' => [
				'dispatcher.php' => $dispatcherConfigContent
			]
		]);
	}

	public function testDispatchMatchedRoute() {
		$path = $this->createMock(Path::class);
		$router = $this->createMock(Router::class);
		$response = $this->createMock(Response::class);
		$session = $this->createMock(Session::class);
		$handlerFactory = $this->createMock(HandlerFactory::class);
		$handler = $this->getMockBuilder(stdClass::class)
			->setMethods(['index'])
			->getMock();

		$path->expects($this->any())
			->method('getConfigurationsPath')
			->willReturn(vfsStream::url('root/config'));

		$router->expects($this->once())
			->method('match')
			->willReturn(true);

		$router->expects($this->once())
			->method('getMatchedHandler')
			->willReturn(['Home.index']);

		$session->expects($this->once())
			->method('clearFlash')
			->will($this->returnSelf());

		$handlerFactory->expects($this->once())
			->method('create')
			->with($this->equalTo('App\Handlers\Home'), 'index')
			->willReturn([$handler, []]);

		$handler->expects($this->once())
			->method('index');

		$dispatcher = new Dispatcher($path, $router, $response, $session, $handlerFactory);
		$dispatcher->dispatch();
	}

	public function testDispatchUnmatchedRoute() {
		$path = $this->createMock(Path::class);
		$router = $this->createMock(Router::class);
		$response = $this->createMock(Response::class);
		$session = $this->createMock(Session::class);
		$handlerFactory = $this->createMock(HandlerFactory::class);
		$notFoundHandler = $this->getMockBuilder(stdClass::class)
			->setMethods(['displayErrorPage'])
			->getMock();

		$path->expects($this->any())
			->method('getConfigurationsPath')
			->willReturn(vfsStream::url('root/config'));

		$router->expects($this->once())
			->method('match')
			->willReturn(false);

		$router->expects($this->once())
			->method('getMatchedStatus')
			->willReturn(404);

		$router->expects($this->once())
			->method('getMatchedHandler')
			->willReturn('NotFoundHandler.displayErrorPage');

		$handlerFactory->expects($this->once())
			->method('create')
			->with($this->equalTo('App\Handlers\NotFoundHandler'), 'displayErrorPage')
			->willReturn([$notFoundHandler, []]);

		$response->expects($this->once())
			->method('setStatusCode')
			->with($this->equalTo(404))
			->will($this->returnSelf());

		$dispatcher = new Dispatcher($path, $router, $response, $session, $handlerFactory);
		$dispatcher->dispatch();
	}
}
