<?php
namespace Rise\Test\ContainerTest;

class ConfigConstructor {
	public function __construct(BaseBinding $dep) {
		$this->dep = $dep;
	}

	public function getDep() {
		return $this->dep;
	}
}
