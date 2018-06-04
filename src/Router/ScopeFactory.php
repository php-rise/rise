<?php
namespace Rise\Router;

use Rise\Container\BaseFactory;

class ScopeFactory extends BaseFactory {
	public function create() {
		return $this->container->getNewInstance('Rise\Router\Scope');
	}
}
