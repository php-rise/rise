<?php
namespace Rise\Test\RouterTest;

use Rise\Router\ScopeFactory as BaseScopeFactory;

class ScopeFactory extends BaseScopeFactory {
	public function __construct() {
	}

	public function create() {
		return new Scope();
	}
}
