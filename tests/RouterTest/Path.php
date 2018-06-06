<?php
namespace Rise\Test\RouterTest;

use Rise\Path as BasePath;

class Path extends BasePath {
	public function __construct() {
		$this->setProjectRootPath(realpath(__DIR__ . '/..'));
	}
}
