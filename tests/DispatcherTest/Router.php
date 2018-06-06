<?php
namespace Rise\Test\DispatcherTest;

use Rise\Router as BaseRouter;

class Router extends BaseRouter {
	public function __construct($matched, $matchedStatus) {
		$this->matched = $matched;
		$this->matchedStatus = $matchedStatus;
	}

	public function match() {
		return $this->matched;
	}
}
