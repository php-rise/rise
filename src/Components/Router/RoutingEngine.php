<?php
namespace Rise\Components\Router;

use TreeRoute\Router as BaseRoutingEngine;

class RoutingEngine extends BaseRoutingEngine {
	/**
	 * Named routes data.
	 *
	 * Format: [
	 *     '<name>' => [
	 *         '<string chunk>',
	 *         ['<variable chunk, param name>'],
	 *         ...
	 *     ],
	 *     ...
	 * ]
	 *
	 * @var array
	 */
	protected $namedRoutes = [];

	/**
	 * Same as the original class.
	 *
	 * {@inheritdoc}
	 */
	private $routes = ['childs' => [], 'regexps' => []];

	/**
	 * @return array
	 */
	public function getNamedRoutes() {
		return $this->namedRoutes;
	}

	/**
	 * @param array $namedRoutes
	 * @return self
	 */
	public function setNamedRoutes($namedRoutes) {
		$this->namedRoutes = $namedRoutes;
		return $this;
	}

	/**
	 * Same as the original class and add support for named route.
	 *
	 * @param string|array $methods
	 * @param string $route
	 * @param mixed $handler
	 * @param string $name optional
	 */
	public function addRoute($methods, $route, $handler, $name = '') {
		$methods = (array) $methods;

		// ADD: Prepare named route params data
		if ($name) {
			$routeData = [];
		}
		// ADD_END

		$parts = explode('/', preg_replace(self::SEPARATOR_REGEXP, '', $route));
		if (sizeof($parts) === 1 && $parts[0] === '') {
			$parts = [];
		}

		$current = &$this->routes;
		for ($i = 0, $length = sizeof($parts); $i < $length; $i++) {
			$paramsMatch = preg_match(self::PARAM_REGEXP, $parts[$i], $paramsMatches);
			if ($paramsMatch) {
				if (!empty($paramsMatches[2])) {
					if (!isset($current['regexps'][$paramsMatches[4]])) {
						$current['regexps'][$paramsMatches[4]] = ['childs' => [], 'regexps' => [], 'name' => $paramsMatches[3]];
					}
					$current = &$current['regexps'][$paramsMatches[4]];
					// ADD: Append data to named route data
					if ($name) {
						$routeData[] = $paramsMatches[3];
					}
					// ADD_END
				} else {
					if (!isset($current['others'])) {
						$current['others'] = ['childs' => [], 'regexps' => [], 'name' => $paramsMatches[5]];
					}
					$current = &$current['others'];
					// ADD: Append data to named route data
					if ($name) {
						$routeData[] = [$paramsMatches[5]];
					}
					// ADD_END
				}
			} else {
				if (!isset($current['childs'][$parts[$i]])) {
					$current['childs'][$parts[$i]] = ['childs' => [], 'regexps' => []];
				}
				$current = &$current['childs'][$parts[$i]];
				// ADD: Append data to named route data
				if ($name) {
					$routeData[] = $parts[$i];
				}
				// ADD_END
			}
		}

		// ADD: Store named route data
		if ($name) {
			$this->namedRoutes[$name] = $routeData;
		}
		// ADD_END

		$current['route'] = $route;
		for ($i = 0, $length = sizeof($methods); $i < $length; $i++) {
			if (!isset($current['methods'])) {
				$current['methods'] = [];
			}
			$current['methods'][strtoupper($methods[$i])] = $handler;
		}
	}

	public function options($route, $handler, $name = '') {
		$this->addRoute('OPTIONS', $route, $handler, $name);
	}

	public function get($route, $handler, $name = '') {
		$this->addRoute('GET', $route, $handler, $name);
	}

	public function head($route, $handler, $name = '') {
		$this->addRoute('HEAD', $route, $handler, $name);
	}

	public function post($route, $handler, $name = '') {
		$this->addRoute('POST', $route, $handler, $name);
	}

	public function put($route, $handler, $name = '') {
		$this->addRoute('PUT', $route, $handler, $name);
	}

	public function delete($route, $handler, $name = '') {
		$this->addRoute('DELETE', $route, $handler, $name);
	}

	public function trace($route, $handler, $name = '') {
		$this->addRoute('TRACE', $route, $handler, $name);
	}

	public function connect($route, $handler, $name = '') {
		$this->addRoute('CONNECT', $route, $handler, $name);
	}

	/**
	 * Get url path by name.
	 *
	 * @param string $name
	 * @param array $params optional
	 * @param bool $trailingSlash optional
	 * @return string
	 */
	public function generatePath($name = '', $params = [], $trailingSlash = false) {
		if (!array_key_exists($name, $this->namedRoutes)) {
			return null;
		}
		$chunks = $this->namedRoutes[$name];
		$path = '/';
		foreach ($chunks as $chunk) {
			if (is_string($chunk)) {
				$path .= $chunk;
			} elseif (is_array($chunk)) {
				$key = $chunk[0];
				if (isset($params[$key])) {
					$path .= $params[$key];
				}
			}
			$path .= '/';
		}
		if (!$trailingSlash && $path !== '/') {
			$path = rtrim($path, '/');
		}
		return $path;
	}

	/**
	 * Same as the original class.
	 *
	 * {@inheritdoc}
	 */
	private function match($url) {
		$parts = explode('?', $url, 2);
		$parts = explode('/', preg_replace(self::SEPARATOR_REGEXP, '', $parts[0]));
		if (sizeof($parts) === 1 && $parts[0] === '') {
			$parts = [];
		}
		$params = [];
		$current = $this->routes;

		for ($i = 0, $length = sizeof($parts); $i < $length; $i++) {
			if (isset($current['childs'][$parts[$i]])) {
				$current = $current['childs'][$parts[$i]];
			} else {
				foreach ($current['regexps'] as $regexp => $route) {
					if (preg_match('/^' . addcslashes($regexp, '/') . '$/', $parts[$i])) {
						$current = $route;
						$params[$current['name']] = $parts[$i];
						continue 2;
					}
				}

				if (!isset($current['others'])) {
					return null;
				}

				$current = $current['others'];
				$params[$current['name']] = $parts[$i];
			}
		}

		if (!isset($current['methods'])) {
			return null;
		}

		return [
			'methods' => $current['methods'],
			'route' => $current['route'],
			'params' => $params
		];
	}

	/**
	 * Same as the original class.
	 *
	 * {@inheritdoc}
	 */
	public function getOptions($url) {
		$route = $this->match($url);
		if (!$route) {
			return null;
		}
		return array_keys($route['methods']);
	}

	/**
	 * Same as the original class.
	 *
	 * {@inheritdoc}
	 */
	public function dispatch($method, $url) {
		$route = $this->match($url);

		if (!$route) {
			return [
				'error' => [
					'code' => 404,
					'message' => 'Not Found'
				],
				'method' => $method,
				'url' => $url
			];
		}

		if (isset($route['methods'][$method])) {
			return [
				'method' => $method,
				'url' => $url,
				'route' => $route['route'],
				'params' => $route['params'],
				'handler' => $route['methods'][$method]
			];
		}

		return [
			'error' => [
				'code' => 405,
				'message' => 'Method Not Allowed'
			],
			'method' => $method,
			'url' => $url,
			'route' => $route['route'],
			'params' => $route['params'],
			'allowed' => array_keys($route['methods'])
		];
	}

	/**
	 * Same as the original class.
	 *
	 * {@inheritdoc}
	 */
	public function getRoutes() {
		return $this->routes;
	}

	/**
	 * Same as the original class. Add return value for method chaining.
	 *
	 * @return self
	 */
	public function setRoutes($routes) {
		$this->routes = $routes;
		return $this;
	}
}
