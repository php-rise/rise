<?php
namespace Rise;

use Rise\Services;

final class ServiceLocator {
	/**
	 * Singleton.
	 *
	 * @var self
	 */
	protected static $instance;

	/**
	 * Array of services.
	 *
	 * Format: [
	 *     '<name>' => <instanceof \Rise\Services\BaseService>,
	 *     ...
	 * ]
	 *
	 * @var array
	 */
	protected $services = [];

	/**
	 * Namespaces of lazy loading services. The last one has the highest priority.
	 *
	 * @var string[]
	 */
	protected $namespaces = ['\Rise\Services'];

	/**
	 * Get singleton instance.
	 *
	 * @return self
	 */
	public static function getInstance() {
		if (!isset(static::$instance)) {
			static::$instance = new static;
		}
		return static::$instance;
	}

	/**
	 * Get a service.
	 *
	 * @param string $name Service name.
	 * @return \Rise\Services\BaseService|null
	 */
	public function getService($name = '') {
		if (isset($this->services[$name])) {
			if (is_string($this->services[$name])) {
				$class = null;
				$service = $this->services[$name];
				$namespace = end($this->namespaces);
				do {
					$class = $namespace . '\\' . $service;
					if (class_exists($class)) {
						break;
					}
					$class = null;
				} while ($namespace = prev($this->namespaces));

				if ($class === null) {
					return null;
				}

				$this->services[$name] = new $class;
			}
			return $this->services[$name];
		}
		return null;
	}

	/**
	 * Register a service.
	 *
	 * @param string $name Service name.
	 * @param \Rise\Services\BaseService|string $service An instance of service or the class name of service. Passing string will result in lazy loading the service.
	 * @return self
	 */
	public function setService($name, $service) {
		if ($service instanceof Services\BaseService
			|| is_string($service)
		) {
			$this->services[$name] = $service;
		}
		return $this;
	}

	/**
	 * Remove a service.
	 *
	 * @param string $name Service name.
	 * @return self
	 */
	public function unsetService($name) {
		unset($this->services[$name]);
		return $this;
	}

	/**
	 * Alias a service.
	 *
	 * @param string $aliasName
	 * @param string $serviceName
	 * @return self
	 */
	public function alias($aliasName, $serviceName) {
		$this->services[$aliasName] = $this->getService($serviceName);
		return $this;
	}

	/**
	 * Add namespace for the trial of creating instance of lazy loading service.
	 *
	 * @param string $namespace
	 * @return self
	 */
	public function addNamespace($namespace = '') {
		if ($namespace) {
			$this->namespaces[] = $namespace;
		}
		return $this;
	}
}
