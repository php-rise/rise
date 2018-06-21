<?php
namespace Rise\Test\ContainerTest;

use Closure;

class MethodInjectionWithExtraMappings {
	public function injectA(DependencyA $a, Closure $next) {
	}
}
