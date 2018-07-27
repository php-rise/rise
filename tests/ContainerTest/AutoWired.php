<?php
namespace Rise\Test\ContainerTest;

class AutoWired {
	public function __construct(DependencyA $a, DependencyB $b) {
		$this->a = $a;
		$this->b = $b;
	}
}
