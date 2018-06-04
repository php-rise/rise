<?php
namespace Rise\Http\Responder;

use Rise\Container\BaseFactory;

class ResponseFactory extends BaseFactory {
	public function create() {
		return $this->container->get('Rise\Http\Responder\Response');
	}
}
