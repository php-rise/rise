<?php
namespace Rise\Test\ContainerTest;

class CyclicA {
	public function __construct(CyclicB $c) {
	}
}
