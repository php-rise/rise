<?php
namespace Rise\Container;

class DynamicFactory extends BaseFactory {
	public function create() {
		list($class) = func_get_args();
		return $this->container->get($class);
	}
}
