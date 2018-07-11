<?php
namespace Rise;

use Closure;
use ReflectionClass;
use ReflectionException;
use Rise\Container\NotFoundException;
use Rise\Container\NotInstantiableException;
use Rise\Container\NotAllowedException;
use Rise\Container\CyclicDependencyException;
use Rise\Container\InvalidRuleException;

class Container {
	/**
	 * Alias mappings.
	 *
	 * @var array
	 */
	protected $aliases = [];

	/**
	 * Cache of singletons.
	 *
	 * @var array
	 */
	protected $singletons = [];

	/**
	 * Cache of factories.
	 *
	 * @var array
	 */
	protected $factories = [];

	/**
	 * Cache of ReflectionClass instances.
	 *
	 * @var array
	 */
	protected $reflectionClasses = [];

	/**
	 * Cache of ReflectionMethod instances.
	 *
	 * Format: [
	 *     '<ClassName>' => [
	 *         '<methodName>' => \ReflectionMethod
	 *     ]
	 * ]
	 *
	 * @var array
	 */
	protected $reflectionMethods = [];

	/**
	 * Rules for resolving parameters.
	 *
	 * Format: [
	 *     '<ClassName>' => [
	 *         '<methodName>' => [
	 *             '<TypeName>' => <ClassName or Closure>,
	 *             '<paramName>' => <any value>,
	 *         ]
	 *     ]
	 * ]
	 *
	 * @var array
	 */
	protected $rules = [];

	/**
	 * Hash map of class names.
	 *
	 * @var array
	 */
	protected $resolvingClasses = [];

	public function __construct() {
		$this->singletons['Rise\Container'] = $this;

		// Bind default factories.
		$this->bindFactory('Rise\Container\DynamicFactory');
		$this->bindFactory('Rise\Request\Upload\FileFactory');
		$this->bindFactory('Rise\Router\ScopeFactory');
		$this->bindFactory('Rise\Dispatcher\HandlerFactory');
		$this->bindFactory('Rise\Template\Blocks\BlockFactory');
	}

	/**
	 * Bind a class name to another class.
	 *
	 * @param string $class
	 * @param string $to
	 * @return self
	 */
	public function bind($class, $to) {
		$this->aliases[$class] = $to;
		return $this;
	}

	/**
	 * Register a factory. A factory can only inject the container for
	 * dependency injection. Factories binded by this method can bypass the
	 * reflection API for better performance.
	 *
	 * @param string $class Class name of the factory.
	 * @return self
	 */
	public function bindFactory($class) {
		$this->factories[$class] = null;
		return $this;
	}

	/**
	 * Register a singleton. It is recommended to let the container to resolve
	 * the class automatically.
	 *
	 * @param string $class
	 * @param object $instance
	 * @return self
	 */
	public function bindSingleton($class, $instance) {
		$this->singletons[$class] = $instance;
		return $this;
	}

	/**
	 * Configure constructor parameters of a class.
	 *
	 * @param string $class Class name.
	 * @param array $rules Parameter rules.
	 * @return self
	 */
	public function configClass($class, $rules) {
		$this->configMethod($class, '__construct', $rules);
		return $this;
	}

	/**
	 * Configure method parameters of a class.
	 *
	 * @param string $class Class name.
	 * @param string $method Method name.
	 * @param array $rules
	 * @return self
	 */
	public function configMethod($class, $method, $rules) {
		foreach ($rules as $param => $rule) {
			if (ctype_upper($param[0])
				&& (!is_string($rule) && !($rule instanceof Closure))
			) {
				throw new InvalidRuleException("Type $param only allowed string or Closure as an extra rule.");
			}
		}
		$this->rules[$class][$method] = $rules;
		return $this;
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

		if (isset($this->resolvingClasses[$class])) {
			throw new CyclicDependencyException("Cyclic dependency detected when resolving $class");
		}

		$this->resolvingClasses[$class] = true;

		if (array_key_exists($class, $this->factories)) {
			$instance = $this->getFactory($class);
		} else {
			$instance = $this->getSingleton($class);
		}

		unset($this->resolvingClasses[$class]);

		return $instance;
	}

	/**
	 * Resolve a method for method injection.
	 *
	 * @param string $class
	 * @param string $method
	 * @param array $extraMappings Optional
	 * @return array [$instance, (string)$method, (array)$args]
	 */
	public function getMethod($class, $method, $extraMappings = []) {
		if (isset($this->aliases[$class])) {
			$class = $this->aliases[$class];
		}

		return [
			$this->getSingleton($class),
			$this->resolveArgs($class, $method, " when resolving method $class::$method", $extraMappings)
		];
	}

	/**
	 * Construct an new instance of a class with its dependencies.
	 *
	 * @param string $class
	 * @return object
	 */
	public function getNewInstance($class) {
		if (isset($this->aliases[$class])) {
			$class = $this->aliases[$class];
		}

		$args = $this->resolveArgs($class, '__construct', " when constructing $class");
		if (empty($args)) {
			$instance = new $class;
		} else {
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
		} catch (ReflectionException $e) {
			throw new NotFoundException("Class $className is not found");
		}

		if (!$reflectionClass->isInstantiable()) {
			throw new NotInstantiableException("$className is not an instantiable class");
		}

		$this->reflectionClasses[$className] = $reflectionClass;

		return $reflectionClass;
	}

	/**
	 * Create and cache a ReflectionMethod.
	 *
	 * @param string $className
	 * @param string $methodName
	 * @return \ReflectionMethod
	 */
	protected function getReflectionMethod($className, $methodName = '__construct') {
		if (isset($this->reflectionMethods[$className])
			&& array_key_exists($methodName, $this->reflectionMethods[$className])
		) {
			return $this->reflectionMethods[$className][$methodName];
		}

		try {
			$reflectionClass = $this->getReflectionClass($className);
			if ($methodName === '__construct') {
				$reflectionMethod = $reflectionClass->getConstructor();
			} else {
				$reflectionMethod = $reflectionClass->getMethod($methodName);
			}
			$this->reflectionMethods[$className][$methodName] = $reflectionMethod;
		} catch (ReflectionException $e) {
			throw new NotFoundException("Method $className::$methodName is not found");
		}

		return $reflectionMethod;
	}
	/**
	 * Resolve parameters of ReflectionMethod.
	 *
	 * @param string $className
	 * @param string $methodName
	 * @param string $errorMessageSuffix Optional
	 * @param array $extraMappings Optional
	 * @return array
	 */
	protected function resolveArgs($className, $methodName, $errorMessageSuffix = '', $extraMappings = []) {
		$reflectionMethod = $this->getReflectionMethod($className, $methodName);

		if (is_null($reflectionMethod) && $methodName === '__construct') {
			return [];
		}

		if (!is_array($extraMappings)) {
			$extraMappings = [];
		}

		if (isset($this->rules[$className][$methodName])) {
			$extraMappings += $this->rules[$className][$methodName];
		}

		$args = [];

		try {
			foreach ($reflectionMethod->getParameters() as $param) {
				if (!empty($extraMappings)) {
					$paramName = $param->getName();

					// Add argument according to parameter name.
					if (array_key_exists($paramName, $extraMappings)) {
						$args[] = $extraMappings[$paramName];
						continue;
					}
				}

				$paramType = $param->getType();

				// Disallow primitive types.
				if ($paramType->isBuiltin()) {
					throw new NotAllowedException("Parameter type \"$paramType\" is not allowed" . $errorMessageSuffix);
				}

				$paramClassName = $param->getClass()->getName();

				// Resolve by class.
				if (array_key_exists($paramClassName, $extraMappings)) {
					if (is_string($extraMappings[$paramClassName])) {
						$args[] = $this->get($extraMappings[$paramClassName]);
					} else {
						$args[] = $extraMappings[$paramClassName];
					}
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
