<?php
namespace Rise\Dispatcher;

use Rise\Container\BaseFactory;

class HandlerFactory extends BaseFactory {
	/**
	 * @param string $class
	 * @param string $method
	 * @param callable $next
	 * @return array
	 */
	public function create($class, $method, $next) {
		return $this->container->getMethod($class, $method, ['Closure' => $next]);
	}
}
