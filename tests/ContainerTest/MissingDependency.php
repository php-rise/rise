<?php
namespace Rise\Test\ContainerTest;

class MissingDependency {
	public function __construct(FakeDependency $fake) {
	}
}
