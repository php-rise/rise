<?php
namespace Rise\Dispatcher;

use Rise\Container\BaseFactory;

class HandlerFactory extends BaseFactory {
	public function create() {
		list($class, $method, $next) = func_get_args();
		return $this->container->getMethod($class, $method, ['Closure' => $next]);
	}
}
