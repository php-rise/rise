<?php
namespace Rise\Services;

use ReflectionClass;

class Container extends BaseService {
	/**
	 * @var array
	 */
	private $singletons = [];

	/**
	 * @var array
	 */
	private $factories = [];

	public function __construct() {
		$this->singletons['Rise\Services\Container'] = $this;

		$this->bindFactory('Rise\Factories\Container\DynamicFactory');
		$this->bindFactory('Rise\Factories\Http\RequestFactory');
		$this->bindFactory('Rise\Factories\Http\ResponseFactory');
		$this->bindFactory('Rise\Factories\Http\Upload\FileFactory');
		$this->bindFactory('Rise\Factories\Router\ScopeFactory');
		$this->bindFactory('Rise\Factories\Template\Blocks\BlockFactory');
		$this->bindFactory('Rise\Factories\Template\Blocks\LayoutFactory');
		$this->bindFactory('Rise\Factories\Template\Blocks\LayoutableBlockFactory');
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

		return $this->getFromSingleton($class);
	}

	private function getFromNew($class) {
		$reflectionClass = new ReflectionClass($class);
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

	private function getFromSingleton($class) {
		if (isset($this->singletons[$class])) {
			return $this->singletons[$class];
		}

		$instance = $this->getFromNew($class);
		$this->singletons[$class] = $instance;
		return $instance;
	}
}
