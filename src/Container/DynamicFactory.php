<?php
namespace Rise\Container;

class DynamicFactory extends BaseFactory {
	/**
	 * @param string $class
	 */
	public function create($class) {
		return $this->container->get($class);
	}
}
