<?php
namespace Rise\Test;

use Closure;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Rise\Dispatcher;
use Rise\Dispatcher\HandlerFactory;
use Rise\Path;

final class DispatcherTest extends TestCase {
	private $root;

	public function setUp() {
		$dispatcherConfigContent = <<<PHP
<?php
/**
 * Configurations of dispatcher.
 *
 * "middlewares": List of middlewares / handlers dispatched when application starts.
 *
 * @var array
 */
return [
	'middlewares' => [
		'Rise\Middlewares\Response.run',
		'Rise\Middlewares\Router.run',
	],
];
PHP;

		$this->root = vfsStream::setup('root', null, [
			'config' => [
				'dispatcher.php' => $dispatcherConfigContent,
			]
		]);
	}

	public function testDispatch() {
		$handlerFactory = $this->createMock(HandlerFactory::class);
		$path = $this->createMock(Path::class);
		$sessionMiddleware = $this->getMockBuilder(stdClass::class)
			->setMethods(['setup'])
			->getMock();
		$homeHandler = $this->getMockBuilder(stdClass::class)
			->setMethods(['index'])
			->getMock();
		$sessionMiddlewareSetupNext = ''; // Reference of next middleware
		$homeHandlerIndexNext = ''; // Reference of next middleware

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

		$dispatcher = new Dispatcher($handlerFactory, $path);
		$dispatcher->setHandlers(['App\Middlewares\Session.setup', 'App\Handlers\Home.index']);
		$dispatcher->dispatch();
	}

	public function testReadConfig() {
		$handlerFactory = $this->createMock(HandlerFactory::class);
		$path = $this->createMock(Path::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$dispatcher = new Dispatcher($handlerFactory, $path);
		$dispatcher->readConfig();

		$this->assertAttributeEquals(
			[
				'Rise\Middlewares\Response.run',
				'Rise\Middlewares\Router.run',
			],
			'handlers',
			$dispatcher
		);
	}
}
