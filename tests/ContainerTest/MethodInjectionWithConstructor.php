<?php
namespace Rise\Test\ContainerTest;

class MethodInjectionWithConstructor {
	public function __construct(DependencyA $a) {
		$this->a = $a;
	}

	public function injectB(DependencyB $b) {
	}

	public function injectC(DependencyC $c) {
	}
}
