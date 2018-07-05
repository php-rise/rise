<?php
namespace Rise;

use ReflectionClass;
use ReflectionException;
use Rise\Container\NotFoundException;
use Rise\Container\NotAllowedException;

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

	/**
	 * @var array
	 */
	protected $reflectionClasses = [];

	public function __construct() {
		$this->singletons['Rise\Container'] = $this;

		$this->bindFactory('Rise\Container\DynamicFactory');
		$this->bindFactory('Rise\Upload\FileFactory');
		$this->bindFactory('Rise\Router\ScopeFactory');
		$this->bindFactory('Rise\Template\Blocks\BlockFactory');
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
	 * Register factory.
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
	 * @return object
	 */
	public function get($class) {
		if (isset($this->aliases[$class])) {
			$class = $this->aliases[$class];
		}

		if (array_key_exists($class, $this->factories)) {
			return $this->getFactory($class);
		}

		return $this->getSingleton($class);
	}

	/**
	 * Resolve a method.
	 *
	 * @param string $class
	 * @param string $method
	 * @param array $extraMappings Optional
	 * @return array
	 */
	public function getMethod($class, $method, $extraMappings = []) {
		if (isset($this->aliases[$class])) {
			$class = $this->aliases[$class];
		}

		return [$this->getSingleton($class), $this->getMethodArgs($class, $method, $extraMappings)];
	}

	/**
	 * Construct an new instance of a class with its dependencies.
	 *
	 * @param string $class
	 * @return object
	 */
	public function getNewInstance($class) {
		$reflectionClass = $this->getReflectionClass($class);
		$constructor = $reflectionClass->getConstructor();

		if (is_null($constructor)) {
			$instance = new $class;
		} else {
			$args = $this->resolveArgs($constructor, " when constructing $class");
			$instance = new $class(...$args);
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
	protected function getSingleton($class) {
		if (isset($this->singletons[$class])) {
			return $this->singletons[$class];
		}

		$instance = $this->getNewInstance($class);
		$this->singletons[$class] = $instance;
		return $instance;
	}

	/**
	 * Construct a factory instance and inject this container to the instance.
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
	 * @param string $className
	 * @param string $methodName
	 * @param array $extraMappings Optional
	 * @param \ReflectionClass $reflectionClass Optional
	 * @return array
	 */
	protected function getMethodArgs($className, $methodName, $extraMappings = []) {
		$reflectionClass = $this->getReflectionClass($className);

		try {
			$method = $reflectionClass->getMethod($methodName);
		} catch (ReflectionException $e) {
			throw new NotFoundException("Method $className::$methodName not found");
		}

		return $this->resolveArgs($method, " when resolving method $className::$methodName", $extraMappings);
	}

	/**
	 * Create and cache a ReflectionClass.
	 *
	 * @param string $className
	 * @return \ReflectionClass
	 */
	protected function getReflectionClass($className) {
		if (isset($this->reflectionClasses[$className])) {
			return $this->reflectionClasses[$className];
		}

		try {
			$reflectionClass = new ReflectionClass($className);
			$this->reflectionClasses[$className] = $reflectionClass;
		} catch (ReflectionException $e) {
			throw new NotFoundException("Class $className is not found");
		}

		return $reflectionClass;
	}

	/**
	 * Resolve parameters of ReflectionMethod.
	 *
	 * @param \ReflectionMethod $reflectionMethod
	 * @param string $errorMessageSuffix Optional
	 * @param array $extraMappings Optional
	 * @return array
	 */
	protected function resolveArgs($reflectionMethod, $errorMessageSuffix = '', $extraMappings = []) {
		$args = [];

		try {
			foreach ($reflectionMethod->getParameters() as $param) {
				$paramType = $param->getType();
				if ($paramType->isBuiltin()) {
					throw new NotAllowedException("Parameter type \"$paramType\" is not allowed" . $errorMessageSuffix);
				}
				$paramClassName = $param->getClass()->getName();
				if (is_array($extraMappings) && array_key_exists($paramClassName, $extraMappings)) {
					$args[] = $extraMappings[$paramClassName];
				} else {
					$args[] = $this->get($paramClassName);
				}
			}
		} catch (ReflectionException $e) {
			throw new NotFoundException("Parameter class $paramType is not found" . $errorMessageSuffix);
		}

		return $args;
	}
}
