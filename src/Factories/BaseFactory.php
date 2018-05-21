<?php
namespace Rise\Factories;

use Rise\Services\Container;

abstract class BaseFactory {
	protected $container;

	public function setContainer(Container $container) {
		$this->container = $container;
	}

	abstract public function create();
}
