<?php
namespace Rise\Test\RouterTest;

use Rise\Router as BaseRouter;

class Router extends BaseRouter {
	public function getRoutesFile() {
		return $this->routesFile;
	}

	public function getNotFoundHandler() {
		return $this->notFoundHandler;
	}
}
