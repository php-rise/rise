<?php
namespace Rise;

use Rise\Router\ScopeFactory;
use Rise\Router\Result;
use Rise\Router\RouteNotFoundException;

class Router {
	/**
	 * Location of the routes file.
	 *
	 * @var string
	 */
	protected $routesFile;

	/**
	 * @var \Rise\Router\ScopeFactory
	 */
	protected $scopeFactory;

	/**
	 * @var \Rise\Router\Result
	 */
	protected $result;

	/**
	 * @var \Rise\Path
	 */
	protected $path;

	public function __construct(
		ScopeFactory $scopeFactory,
		Result $result,
		Path $path
	) {
		$this->scopeFactory = $scopeFactory;
		$this->result = $result;
		$this->path = $path;

		$this->readConfig();
	}

	/**
	 * Setup routes.
	 *
	 * @return self
	 */
	public function buildRoutes() {
		$scope = $this->scopeFactory->create();
		require($this->routesFile);
		return $this;
	}

	/**
	 * Match current HTTP request.
	 *
	 * @return mixed
	 */
	public function match() {
		if ($this->result->hasHandler()) {
			return $this->result->getHandler();
		}

		throw new RouteNotFoundException;
	}

	/**
	 * @return self
	 */
	protected function readConfig() {
		$this->routesFile = $this->path->getConfigPath() . '/' . 'routes.php';
		return $this;
	}
}
