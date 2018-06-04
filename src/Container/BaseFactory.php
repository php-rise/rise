<?php
namespace Rise\Container;

use Rise\Container;

abstract class BaseFactory {
	protected $container;

	public function setContainer(Container $container) {
		$this->container = $container;
	}

	abstract public function create();
}
