<?php
namespace Rise\Factories\Router;

use Rise\Factories\BaseFactory;

class ScopeFactory extends BaseFactory {
	public function create() {
		return $this->container->get('Rise\Components\Router\Scope');
	}
}
