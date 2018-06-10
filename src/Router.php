<?php
namespace Rise;

use Rise\Http\Request;
use Rise\Router\RoutingEngine;
use Rise\Router\ScopeFactory;

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
	 * @var \Rise\Router\RoutingEngine
	 */
	protected $engine;

	/**
	 * @var \Rise\Router\ScopeFactory
	 */
	protected $scopeFactory;

	/**
	 * @var \Rise\Path
	 */
	protected $path;

	/**
	 * @var \Rise\Http\Request
	 */
	protected $request;

	/**
	 * @var \Rise\Locale
	 */
	protected $locale;

	public function __construct(
		RoutingEngine $engine,
		ScopeFactory $scopeFactory,
		Path $path,
		Request $request,
		Locale $locale
	) {
		$this->engine = $engine;
		$this->scopeFactory = $scopeFactory;
		$this->path = $path;
		$this->request = $request;
		$this->locale = $locale;

		$this->readConfigurations();
	}

	/**
	 * @return self
	 */
	public function readConfigurations() {
		$configurations = require($this->path->getConfigurationsPath() . '/router.php');
		$this->routesFile = $this->path->getProjectRootPath() . '/' . $configurations['routesFile'];
		if (isset($configurations['notFoundHandler'])) {
			$this->notFoundHandler = $configurations['notFoundHandler'];
		}
		return $this;
	}

	/**
	 * Setup routes.
	 *
	 * @return self
	 */
	public function buildRoutes() {
		$scope = $this->scopeFactory->create();
		$scope->setEngine($this->engine);
		require($this->routesFile);
		return $this;
	}

	/**
	 * Match current HTTP request.
	 *
	 * @return bool
	 */
	public function match() {
		$request = $this->request;
		if ($request->isMethod('POST') && $request->getInput('_method')) {
			$result = $this->engine->dispatch(
				strtoupper($request->getInput('_method', $request->getMethod())),
				$request->getRequestPath()
			);
		} else {
			$result = $this->engine->dispatch($request->getMethod(), $request->getRequestPath());
		}

		if (isset($result['error'])) {
			switch ($result['error']['code']) {
			case 404:
				$this->matchedStatus = 404;
				$this->matchedHandler = $this->notFoundHandler;
				return false;
			case 405:
				$this->matchedStatus = 405;
				$this->matchedHandler = $this->notFoundHandler;
				return false;
			}
		} else {
			$this->matchedStatus = 200;
			$this->matchedHandler = $result['handler'];
			$request->setParams($result['params']);
			return true;
		}
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
	 * Generate URL of named route
	 *
	 * @param string $name
	 * @param array $params
	 * @return string
	 */
	public function generateUrl($name = '', $params = []) {
		if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
			$scheme = 'https';
		} else {
			$scheme = 'http';
		}
		return $scheme . '://' . $_SERVER['HTTP_HOST'] . $this->generatePath($name, $params);
	}

	/**
	 * Generate URL path of named route
	 *
	 * @param string $name
	 * @param array $params
	 * @param string $localeCode
	 * @return string
	 */
	public function generatePath($name = '', $params = [], $localeCode = null) {
		if (!$localeCode) {
			$localeCode = $this->locale->getCurrentLocaleCode();
		}

		if ($localeCode) {
			return '/' . $localeCode . $this->engine->generatePath($name, $params);
		}

		return $this->engine->generatePath($name, $params);
	}
}
