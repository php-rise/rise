<?php
namespace Rise\Test\ContainerTest;

use Rise\Container\BaseFactory;

class Factory extends BaseFactory {
	public function getContainer() {
		return $this->container;
	}

	public function create() {}
}
