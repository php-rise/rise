<?php
namespace Rise\Test\ContainerTest;

class MethodInjectionWithoutConstructor {
	public function injectB(DependencyB $b) {
	}

	public function injectC(DependencyC $c) {
	}
}
