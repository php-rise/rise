<?php
namespace Rise;

class Initializer {
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
		$this->container->get(Router::class)->buildRoutes();
		$this->container->get(Dispatcher::class)->dispatch();
		return $this;
	}
}
