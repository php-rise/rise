<?php
namespace Rise\Services;

use Rise\Components\Router\RoutingEngine;
use Rise\Components\Router\Scope;

class Router extends BaseService {
	/**
	 * Location of the routes file.
	 *
	 * @var string
	 */
	protected $routesFile;

	/**
	 * @var \Rise\Components\Router\RoutingEngine
	 */
	protected $engine;

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
	 * @return self
	 */
	public function readConfigurations() {
		$configurations = require(service('path')->getConfigurationsPath() . '/router.php');
		$this->routesFile = service('path')->getProjectRootPath() . '/' . $configurations['routesFile'];
		return $this;
	}

	/**
	 * Setup routes.
	 *
	 * @return self
	 */
	public function buildRoutes() {
		$engine = new RoutingEngine;
		$scope = new Scope;
		$scope->setEngine($engine);

		require($this->routesFile);

		$this->engine = $engine;
		return $this;
	}

	/**
	 * Match current HTTP request.
	 *
	 * @return bool
	 */
	public function match() {
		$request = service('http')->getRequest();
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
				return false;
			case 405:
				$this->matchedStatus = 405;
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
			$localeCode = service('locale')->getCurrentLocaleCode();
		}

		if ($localeCode) {
			return '/' . $localeCode . '/'
				. $this->engine->generatePath($name, $params);
		}

		return $this->engine->generatePath($name, $params);
	}
}
