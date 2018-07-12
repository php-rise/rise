<?php
namespace Rise;

use Rise\Router\ScopeFactory;
use Rise\Router\Result;

class Router {
	/**
	 * Location of the routes file.
	 *
	 * @var string
	 */
	protected $routesFile;

	/**
	 * @var string
	 */
	protected $notFoundHandler;

	/**
	 * @var mixed
	 */
	protected $matchedHandler;

	/**
	 * HTTP status code
	 *
	 * @var int
	 */
	protected $matchedStatus;

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
	 * @return bool
	 */
	public function match() {
		$result = false;

		$this->matchedStatus = $this->result->getStatus();

		if ($this->result->hasHandler()) {
			$result = true;
			$this->matchedHandler = $this->result->getHandler();
		} else {
			$this->matchedHandler = $this->notFoundHandler;
		}

		return $result;
	}

	/**
	 * @return mixed
	 */
	public function getMatchedHandler() {
		return $this->matchedHandler;
	}

	/**
	 * @return int
	 */
	public function getMatchedStatus() {
		return $this->matchedStatus;
	}

	/**
	 * @return self
	 */
	protected function readConfig() {
		$this->routesFile = $this->path->getConfigPath() . '/' . 'routes.php';
		$configurations = require($this->path->getConfigPath() . '/router.php');
		if (isset($configurations['notFoundHandler'])) {
			$this->notFoundHandler = $configurations['notFoundHandler'];
		}
		return $this;
	}
}
