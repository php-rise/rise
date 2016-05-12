<?php
namespace Rise\Components\Router;

class Scope {
	/**
	 * @var \Rise\Components\Router\Scope
	 */
	protected $parentScope = null;

	/**
	 * @var \Rise\Components\Router\RoutingEngine
	 */
	protected $engine;

	/**
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * @var string
	 */
	protected $namespace = '';

	/**
	 * @var string[]
	 */
	protected $previousHandlers = [];

	/**
	 * @var string|null
	 */
	protected $generatedPrefix = null;

	/**
	 * @var string|null
	 */
	protected $generatedNamespace = null;

	/**
	 * @var string[]|null
	 */
	protected $generatedPreviousHandlers = null;

	/**
	 * @return \Rise\Components\Router\Scope
	 */
	public function getParentScope() {
		return $this->parentScope;
	}

	/**
	 * @param \Rise\Components\Router\Scope
	 * @return self
	 */
	public function setParentScope($parentScope) {
		$this->parentScope = $parentScope;
		return $this;
	}

	/**
	 * @return \Rise\Components\Router\RoutingEngine
	 */
	public function getEngine() {
		return $this->engine;
	}

	/**
	 * @param \Rise\Components\Router\RoutingEngine
	 * @return self
	 */
	public function setEngine($engine) {
		$this->engine = $engine;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPrefix() {
		return $this->prefix;
	}

	/**
	 * @param string $prefix
	 * @return self
	 */
	public function setPrefix($prefix) {
		$this->prefix = $prefix;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getNamespace() {
		return $this->namespace;
	}

	/**
	 * @param string $namespace
	 * @return self
	 */
	public function setNamespace($namespace) {
		$this->namespace = $namespace;
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getPreviousHandlers() {
		return $this->previousHandlers;
	}

	/**
	 * @param string[]|string $previousHandlers
	 * @return self
	 */
	public function setPreviousHandlers($previousHandlers) {
		$this->previousHandlers = (array)$previousHandlers;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getGeneratedPrefix() {
		if ($this->generatedPrefix !== null) {
			return $this->generatedPrefix;
		}
		$generatedPrefix = '';
		$scope = $this;
		do {
			$prefix = $scope->getPrefix();
			if ($prefix) {
				$generatedPrefix = rtrim($prefix, '/') . '/' . ltrim($generatedPrefix, '/');
			}
			$scope = $scope->getParentScope();
		} while ($scope !== null);
		$this->generatedPrefix = $generatedPrefix;
		return $generatedPrefix;
	}

	/**
	 * @return string
	 */
	public function getGeneratedNamespace() {
		if ($this->generatedNamespace !== null) {
			return $this->generatedNamespace;
		}
		$generatedNamespace = '';
		$scope = $this;
		do {
			$namespace = $scope->getNamespace();
			if ($namespace) {
				$generatedNamespace = rtrim($namespace, '\\') . '\\' . ltrim($generatedNamespace, '\\');
			}
			$scope = $scope->getParentScope();
		} while ($scope !== null);
		if ($generatedNamespace === '\\') {
			$generatedNamespace = '';
		}
		$this->generatedNamespace = $generatedNamespace;
		return $generatedNamespace;
	}

	/**
	 * @return string[]
	 */
	public function getGeneratedPreviousHandlers() {
		if ($this->generatedPreviousHandlers !== null) {
			return $this->generatedPreviousHandlers;
		}
		$generatedPreviousHandlers = [];
		$scope = $this;
		do {
			$generatedPreviousHandlers = array_merge($scope->getPreviousHandlers(), $generatedPreviousHandlers);
			$scope = $scope->getParentScope();
		} while ($scope !== null);
		$this->generatedPreviousHandlers = $generatedPreviousHandlers;
		return $generatedPreviousHandlers;
	}

	/**
	 * @param callable $closure
	 * @return self
	 */
	public function createScope($closure) {
		$newScope = (new static)->setParentScope($this)
			->setEngine($this->engine);
		$closure($newScope);
		return $this;
	}

	/**
	 * @param string|array $methods
	 * @param string $path
	 * @param mixed $handler
	 * @param string $name Route name.
	 * @return self
	 */
	public function addRoute($methods = [], $path = '', $handler, $name = '') {
		$path = $this->getGeneratedPrefix() . ltrim($path, '/');

		$handlers = (array)$handler;
		$namespace = $this->getGeneratedNamespace();
		if ($namespace) {
			foreach ($handlers as &$handler) {
				$handler = $namespace . $handler;
			}
		}

		$previousHandlers = $this->getGeneratedPreviousHandlers();
		if (!empty($previousHandlers)) {
			$handlers = array_merge($previousHandlers, $handlers);
		}

		$this->engine->addRoute($methods, $path, $handlers, $name);
	}

	public function options($path, $handler, $name = '') {
		$this->addRoute('OPTIONS', $path, $handler, $name);
	}

	public function get($path, $handler, $name = '') {
		$this->addRoute('GET', $path, $handler, $name);
	}

	public function head($path, $handler, $name = '') {
		$this->addRoute('HEAD', $path, $handler, $name);
	}

	public function post($path, $handler, $name = '') {
		$this->addRoute('POST', $path, $handler, $name);
	}

	public function put($path, $handler, $name = '') {
		$this->addRoute('PUT', $path, $handler, $name);
	}

	public function delete($path, $handler, $name = '') {
		$this->addRoute('DELETE', $path, $handler, $name);
	}

	public function trace($path, $handler, $name = '') {
		$this->addRoute('TRACE', $path, $handler, $name);
	}

	public function connect($path, $handler, $name = '') {
		$this->addRoute('CONNECT', $path, $handler, $name);
	}
}
