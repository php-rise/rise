<?php
namespace Rise\Test\ContainerTest;

class Cyclic {
	public function __construct(Cyclic $c) {
	}
}
