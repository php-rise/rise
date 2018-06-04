<?php
namespace Rise\Http\Receiver;

use Rise\Container\BaseFactory;

class RequestFactory extends BaseFactory {
	public function create() {
		return $this->container->get('Rise\Http\Receiver\Request');
	}
}
