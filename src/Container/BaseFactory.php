<?php
namespace Rise\Container;

use Rise\Container;

abstract class BaseFactory {
	protected $container;

	public function __construct(Container $container) {
		$this->container = $container;
	}

	abstract public function create();
}
