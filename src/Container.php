<?php
namespace Rise;

use ReflectionClass;
use ReflectionException;
use Rise\Container\NotFoundException;

class Container {
	/**
	 * @var array
	 */
	protected $aliases = [];

	/**
	 * @var array
	 */
	protected $singletons = [];

	/**
	 * @var array
	 */
	protected $factories = [];

	public function __construct() {
		$this->singletons['Rise\Container'] = $this;

		$this->bindFactory('Rise\Container\DynamicFactory');
		$this->bindFactory('Rise\Http\Upload\FileFactory');
		$this->bindFactory('Rise\Router\ScopeFactory');
		$this->bindFactory('Rise\Template\Blocks\BlockFactory');
		$this->bindFactory('Rise\Template\Blocks\LayoutFactory');
		$this->bindFactory('Rise\Template\Blocks\LayoutableBlockFactory');
	}

	/**
	 * Bind a class name to another class.
	 *
	 * @param string $class
	 * @param string $to
	 */
	public function bind($class, $to) {
		$this->aliases[$class] = $to;
	}

	/**
	 * Construct a factory instance and inject this container to the instance.
	 *
	 * @param string $factory
	 * @param string $to Optional
	 */
	public function bindFactory($class, $to = null) {
		if (!empty($to)) {
			$this->bind($class, $to);
			$class = $to;
		}

		$this->factories[$class] = null;
	}

	/**
	 * Resolve a class.
	 *
	 * @param string $class
	 * @param string $method Optional
	 * @return object|array
	 */
	public function get($class, $method = null) {
		if (isset($this->aliases[$class])) {
			$class = $this->aliases[$class];
		}

		if (array_key_exists($class, $this->factories)) {
			return $this->getFactory($class);
		}

		return $this->getSingleton($class, $method);
	}

	/**
	 * Construct an new instance of a class with its dependencies.
	 *
	 * @param string $class
	 * @param string $method Optional
	 * @return object|array
	 */
	public function getNewInstance($class, $method = null) {
		try {
			$reflectionClass = new ReflectionClass($class);
		} catch (ReflectionException $e) {
			throw new NotFoundException("Class $class not found");
		}

		$constructor = $reflectionClass->getConstructor();

		if (is_null($constructor)) {
			$instance = new $class;
		} else {
			$args = [];

			try {
				foreach ($constructor->getParameters() as $param) {
					$paramClassName = $param->getClass()->getName();
					array_push($args, $this->get($paramClassName));
				}
			} catch (ReflectionException $e) {
				$paramClassName = (string)$param->getType();
				throw new NotFoundException("Parameter class $paramClassName not found when constructing $class");
			}

			$instance = new $class(...$args);
		}

		if ($method) {
			return [$instance, $this->resolveMethodArgs($class, $method, $reflectionClass)];
		}

		return $instance;
	}

	/**
	 * Get singleton of a class.
	 *
	 * @param string $class
	 * @param string $method Optional
	 * @return object|array
	 */
	protected function getSingleton($class, $method = null) {
		if (isset($this->singletons[$class])) {
			if ($method) {
				return [$this->singletons[$class], $this->resolveMethodArgs($class, $method)];
			} else {
				return $this->singletons[$class];
			}
		}

		if ($method) {
			list ($instance, $methodArgs) = $this->getNewInstance($class, $method);
			$this->singletons[$class] = $instance;
			return [$instance, $methodArgs];
		} else {
			$instance = $this->getNewInstance($class);
			$this->singletons[$class] = $instance;
			return $instance;
		}
	}

	/**
	 * Get a factory instance.
	 *
	 * @param string $class
	 * @return object
	 */
	protected function getFactory($class) {
		if (isset($this->factories[$class])) {
			return $this->factories[$class];
		}

		$factory = new $class($this);
		$this->factories[$class] = $factory;
		return $factory;
	}

	/**
	 * Resolve method parameters for method injection.
	 *
	 * @var param string $className
	 * @param string $methodName
	 * @param \ReflectionClass $reflectionClass Optional
	 * @return array
	 */
	protected function resolveMethodArgs($className, $methodName, $reflectionClass = null) {
		if (!$reflectionClass) {
			try {
				$reflectionClass = new ReflectionClass($className);
			} catch (ReflectionException $e) {
				throw new NotFoundException("Class $className not found");
			}
		}

		try {
			$method = $reflectionClass->getMethod($methodName);
		} catch (ReflectionException $e) {
			throw new NotFoundException("Method $className->$methodName not found");
		}

		$args = [];

		try {
			foreach ($method->getParameters() as $param) {
				$paramClassName = $param->getClass()->getName();
				array_push($args, $this->get($paramClassName));
			}
		} catch (ReflectionException $e) {
			$paramClassName = (string)$param->getType();
			throw new NotFoundException("Parameter class $paramClassName not found when resolving method $className->$methodName");
		}

		return $args;
	}
}
