<?php
namespace Rise\Factories\Http;

use Rise\Factories\BaseFactory;

class RequestFactory extends BaseFactory {
	public function create() {
		return $this->container->get('Rise\Components\Http\Request');
	}
}
