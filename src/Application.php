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
		$router = $this->container->get(Router::class);
		$dispatcher = $this->container->get(Dispatcher::class);
		$response = $this->container->get(Response::class);

		$router->buildRoutes()->match();
		$response->setStatusCode($router->getMatchedStatus());
		$dispatcher->setHandlers($router->getMatchedHandler())->dispatch();
		$response->send()->end();

		return $this;
	}
}
