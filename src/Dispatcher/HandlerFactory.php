<?php
namespace Rise\Dispatcher;

use Rise\Container\BaseFactory;

class HandlerFactory extends BaseFactory {
	/**
	 * @param string $handler Class method expression separate by a dot. e.g. "App\Handlers\Handler.handle"
	 * @param callable $next
	 * @return array
	 */
	public function create($handler, $next) {
		list ($class, $method) = explode('.', $handler, 2);
		list ($instance, $args) = $this->container->getMethod($class, $method, ['Closure' => $next]);
		return [$instance, $method, $args];
	}
}
