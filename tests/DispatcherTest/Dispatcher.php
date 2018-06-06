<?php
namespace Rise\Test\DispatcherTest;

use Rise\Dispatcher as BaseDispatcher;

class Dispatcher extends BaseDispatcher {
	public function getHandlerNamespace() {
		return $this->handlerNamespace;
	}
}
