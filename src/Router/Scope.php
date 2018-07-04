<?php
namespace Rise\Router;

use Exception;
use Rise\Http\Request;

class Scope {
	const ROUTE_PARAM_PATTERN = '/(\\{(.*?)\\})/';

	/**
	 * URL path prefix.
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * Indicates if the prefix is matched with the request path.
	 * @var bool
	 */
	protected $prefixMatched = true;

	/**
	 * Namespace of all handlers in the scope and child scopes.
	 * @var string
	 */
	protected $namespace = '';

	/**
	 * @var string[]
	 */
	protected $middlewares = [];

	/**
	 * Starting offset of request path for testing.
	 * @var int
	 */
	protected $requestPathOffset = 0;

	/**
	 * @var array
	 */
	protected $params = [];

	/**
	 * @var bool
	 */
	protected $hasCalledPrefix = false;

	/**
	 * @var bool
	 */
	protected $hasCalledOn = false;

	/**
	 * @var \Rise\Http\Request
	 */
	protected $request;

	/**
	 * @var \Rise\Router\Result
	 */
	protected $result;

	/**
	 * @var \Rise\Router\UrlGenerator
	 */
	protected $urlGenerator;

	public function __construct(Request $request, Result $result, UrlGenerator $urlGenerator) {
		$this->request = $request;
		$this->result = $result;
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * Create a child scope.
	 *
	 * @param callable $closure
	 */
	public function createScope($closure) {
		$newScope = (new static($this->request, $this->result, $this->urlGenerator));
		$newScope->setupParent(
			$this->prefix,
			$this->prefixMatched,
			$this->requestPathOffset,
			$this->params
		);
		$newScope->use($this->middlewares); // Must add middlewares before adding namespace, as the middlewares are already prefixed with namespace.
		$newScope->namespace($this->namespace);
		$closure($newScope);
	}

	/**
	 * Set a common path prefix for all routes.
	 *
	 * @param string $prefix
	 * @return self
	 */
	public function prefix($prefix) {
		if ($this->hasCalledPrefix) {
			throw new Exception(get_class($this) . '::prefix() should be called only once.');
		}

		if ($this->hasCalledOn) {
			throw new Exception(get_class($this) . '::prefix() should be called before ' . get_class($this) . '::on().');
		}

		$this->hasCalledPrefix = true;

		if (!$this->result->hasHandler() && $this->prefixMatched) {
			$this->prefixMatched = $this->matchPartial($prefix);
		}

		$this->prefix .= $prefix;

		return $this;
	}

	/**
	 * Set a common namespace for all middlewares and handlers.
	 *
	 * @param string $namespace
	 * @return self
	 */
	public function namespace($namespace) {
		if (empty($namespace)) {
			$this->namespace = '';
		} else {
			$this->namespace = rtrim($namespace, '\\') . '\\';
		}
		return $this;
	}

	/**
	 * Add middlewares.
	 *
	 * @param string[]|string $middlewares
	 * @return self
	 */
	public function use($middlewares) {
		$middlewares = (array)$middlewares;

		if ($this->namespace) {
			foreach ($middlewares as &$middleware) {
				$middleware = $this->namespace . $middleware;
			}
		}

		$this->middlewares = array_merge($this->middlewares, (array)$middlewares);

		return $this;
	}

	/**
	 * Add a route.
	 *
	 * @param string $method
	 * @param string $path
	 * @param string|string[] $handler
	 * @param string $name Route name.
	 * @return self
	 */
	public function on($method, $path, $handler, $name = '') {
		$this->hasCalledOn = true;

		if (!$this->result->hasHandler()
			&& $this->prefixMatched
			&& $this->request->isMethod($method)
		) {
			if ($this->matchPartial($path, true)) {
				$handlers = (array)$handler;

				if ($this->namespace) {
					foreach ($handlers as &$handler) {
						$handler = $this->namespace . $handler;
					}
				}

				if (!empty($this->middlewares)) {
					$handlers = array_merge($this->middlewares, $handlers);
				}

				$this->result->setHandler($handlers);
				$this->result->setParams($this->params);
			}
		}

		if (!empty($name)) {
			$this->urlGenerator->add($name, $this->prefix . $path);
		}

		return $this;
	}

	/**
	 * @param string $path
	 * @param string $handler
	 * @param string $name
	 * @return self
	 */
	public function options($path, $handler, $name = '') {
		return $this->on('OPTIONS', $path, $handler, $name);
	}

	public function get($path, $handler, $name = '') {
		return $this->on('GET', $path, $handler, $name);
	}

	public function head($path, $handler, $name = '') {
		return $this->on('HEAD', $path, $handler, $name);
	}

	public function post($path, $handler, $name = '') {
		return $this->on('POST', $path, $handler, $name);
	}

	public function put($path, $handler, $name = '') {
		return $this->on('PUT', $path, $handler, $name);
	}

	public function delete($path, $handler, $name = '') {
		return $this->on('DELETE', $path, $handler, $name);
	}

	public function trace($path, $handler, $name = '') {
		return $this->on('TRACE', $path, $handler, $name);
	}

	public function connect($path, $handler, $name = '') {
		return $this->on('CONNECT', $path, $handler, $name);
	}

	/**
	 * Inherit data from parent scope.
	 *
	 * @param string $prefix
	 * @param bool $prefixMatched
	 * @param int $requestPathOffset
	 * @param array $params
	 */
	public function setupParent($prefix, $prefixMatched, $requestPathOffset, $params) {
		$this->prefix = $prefix;
		$this->prefixMatched = $prefixMatched;
		$this->requestPathOffset = $requestPathOffset;
		$this->params = $params;
	}

	/**
	 * @param string $routePathPartial,
	 * @param bool $toEnd
	 * @return bool
	 */
	protected function matchPartial($routePathPartial, $toEnd = false) {
		$result = false;

		$numOfRouteMatches = preg_match_all(
			self::ROUTE_PARAM_PATTERN,
			$routePathPartial,
			$routeMatches,
			PREG_OFFSET_CAPTURE
		);

		if ($numOfRouteMatches === 0) { // plain string
			if ($toEnd) {
				$requestPathPartial = substr($this->request->getRequestPath(), $this->requestPathOffset);
			} else {
				$requestPathPartial = substr($this->request->getRequestPath(), $this->requestPathOffset, strlen($routePathPartial));
			}

			if ($routePathPartial === $requestPathPartial) {
				$result = true;

				if (!$toEnd) {
					// Move offset if it is not matching to the end, i.e. not the last match.
					$this->requestPathOffset += strlen($routePathPartial);
				}
			}
		} else if ($numOfRouteMatches > 0) { // has params
			// Build regex
			$pos = 0;
			$pattern = '#^';
			for ($i = 0; $i < $numOfRouteMatches; $i++) {
				$pattern .= substr($routePathPartial, $pos, $routeMatches[1][$i][1] - $pos);
				$pattern .= '(?P<' . $routeMatches[2][$i][0] . '>[^/]+)';
				$pos = $routeMatches[1][$i][1] + strlen($routeMatches[1][$i][0]);
			}
			$pattern .= substr($routePathPartial, $pos);
			if ($toEnd) {
				$pattern .= '$';
			}
			$pattern .= '#';

			$requestPathPartial = substr($this->request->getRequestPath(), $this->requestPathOffset);

			$numOfPathMatches = preg_match($pattern, $requestPathPartial, $pathMatches);

			if ($numOfPathMatches === 1) {
				$result = true;

				// Setup params
				foreach ($routeMatches[2] as $m) {
					$paramName = $m[0];
					$this->params[$paramName] = $pathMatches[$paramName];
				}

				if (!$toEnd) {
					// Move offset if it is not matching to the end, i.e. not the last match.
					$this->requestPathOffset += strlen($pathMatches[0]);
				}
			}
		}

		return $result;
	}
}
