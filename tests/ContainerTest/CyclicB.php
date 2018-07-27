<?php
namespace Rise\Test\ContainerTest;

class CyclicB {
	public function __construct(CyclicB $c) {
	}
}
