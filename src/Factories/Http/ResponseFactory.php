<?php
namespace Rise\Factories\Http;

use Rise\Factories\BaseFactory;

class ResponseFactory extends BaseFactory {
	public function create() {
		return $this->container->get('Rise\Components\Http\Response');
	}
}
