<?php
namespace Rise\Services;

use ReflectionClass;

class Container extends BaseService {
	const STORE_SINGLETON = 1;
	const STORE_FACTORY = 2;

	/**
	 * @var array
	 */
	private $store = [];

	/**
	 * @var array
	 */
	private $singletons = [];

	/**
	 * @var array
	 */
	private $factories = [];

	public function __construct() {
		$this->bindSingleton('Rise\Services\Container');
		$this->singletons['Rise\Services\Container'] = $this;

		$this->bindSingleton('Rise\Services\Initializer');
		$this->bindSingleton('Rise\Services\Path');
		$this->bindSingleton('Rise\Services\Http\Receiver');
		$this->bindSingleton('Rise\Services\Http\Responder');
		$this->bindSingleton('Rise\Services\Http\Upload');
		$this->bindSingleton('Rise\Services\Database');
		$this->bindSingleton('Rise\Services\Locale');
		$this->bindSingleton('Rise\Services\Session');
		$this->bindSingleton('Rise\Services\Router');
		$this->bindSingleton('Rise\Services\Dispatcher');
		$this->bindSingleton('Rise\Services\Template');

		$this->bindFactory('Rise\Factories\Container\DynamicFactory');
		$this->bindFactory('Rise\Factories\Http\RequestFactory');
		$this->bindFactory('Rise\Factories\Http\ResponseFactory');
		$this->bindFactory('Rise\Factories\Template\Blocks\BlockFactory');
		$this->bindFactory('Rise\Factories\Template\Blocks\LayoutFactory');
		$this->bindFactory('Rise\Factories\Template\Blocks\LayoutableBlockFactory');
	}

	/**
	 * @param string $class
	 */
	public function bindSingleton($class) {
		$this->store[$class] = self::STORE_SINGLETON;
	}

	/**
	 * @param string $class
	 * @param string|\Rise\Factories\BaseFactory $factory
	 */
	public function bindFactory($class, $factory = '') {
		$this->store[$class] = self::STORE_FACTORY;
		if (empty($factory)) {
			$factory = $class;
		}
		if (is_string($factory)) {
			$factory = new $factory;
		}
		$factory->setContainer($this);
		$this->factories[$class] = $factory;
	}

	/**
	 * Resolve a class.
	 *
	 * @param string $class
	 * @return object|callable
	 */
	public function get($class) {
		if (isset($this->store[$class])) {
			return $this->getFromStore($class);
		}

		return $this->getFromNew($class);
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

	private function getFromStore($class) {
		if ($this->store[$class] === self::STORE_SINGLETON) {
			return $this->getFromSingleton($class);
		}

		if ($this->store[$class] === self::STORE_FACTORY) {
			return $this->factories[$class];
		}
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
