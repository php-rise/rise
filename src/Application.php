<?php
namespace Rise;

class Application {
	/**
	 * @var \Rise\Container
	 */
	protected $container;

	/**
	 * @var \Rise\Path
	 */
	protected $path;

	public function __construct(Container $container, Path $path) {
		$this->container = $container;
		$this->path = $path;
	}

	/**
	 * @param string $projectRootPath
	 * @return self
	 */
	public function setProjectRoot($projectRootPath) {
		$this->path->setProjectRootPath($projectRootPath);
		return $this;
	}

	/**
	 * @return self
	 */
	public function run() {
		$dispatcher = $this->container->get(Dispatcher::class);

		// Set default middlewares
		$dispatcher->setHandlers([
			'Rise\Middlewares\Response.run',
			'Rise\Middlewares\Router.run',
		]);

		// Dispatch
		$dispatcher->readConfig()->dispatch();

		return $this;
	}
}
