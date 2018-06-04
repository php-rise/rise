<?php
namespace Rise;

use ReflectionClass;
use ReflectionException;
use Rise\Container\NotFoundException;

class Container {
	/**
	 * @var array
	 */
	private $singletons = [];

	/**
	 * @var array
	 */
	private $factories = [];

	public function __construct() {
		$this->singletons['Rise\Container'] = $this;

		$this->bindFactory('Rise\Container\DynamicFactory');
		$this->bindFactory('Rise\Http\Receiver\RequestFactory');
		$this->bindFactory('Rise\Http\Responder\ResponseFactory');
		$this->bindFactory('Rise\Http\Upload\FileFactory');
		$this->bindFactory('Rise\Router\ScopeFactory');
		$this->bindFactory('Rise\Template\Blocks\BlockFactory');
		$this->bindFactory('Rise\Template\Blocks\LayoutFactory');
		$this->bindFactory('Rise\Template\Blocks\LayoutableBlockFactory');
	}

	/**
	 * @param string $factory
	 */
	public function bindFactory($class) {
		$factory = new $class;
		$factory->setContainer($this);
		$this->factories[$class] = $factory;
	}

	/**
	 * Resolve a class.
	 *
	 * @param string $class
	 * @return object
	 */
	public function get($class) {
		if (isset($this->factories[$class])) {
			return $this->factories[$class];
		}

		return $this->getSingleton($class);
	}

	public function getNewInstance($class) {
		try {
			$reflectionClass = new ReflectionClass($class);
		} catch (ReflectionException $e) {
			throw new NotFoundException("Class $class not found");
		}

		$constructor = $reflectionClass->getConstructor();

		if (is_null($constructor)) {
			return new $class;
		}

		$params =  $constructor->getParameters();
		$args = [];
		foreach ($params as $param) {
			$paramClassName = $param->getClass()->getName();
			array_push($args, $this->get($paramClassName));
		}
		return new $class(...$args);
	}

	private function getSingleton($class) {
		if (isset($this->singletons[$class])) {
			return $this->singletons[$class];
		}

		$instance = $this->getNewInstance($class);
		$this->singletons[$class] = $instance;
		return $instance;
	}
}
