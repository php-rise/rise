<?php
namespace Rise\Components\Command;

abstract class BaseCommand {
	protected $arguments = [];

	public function setArguments($arguments) {
		$this->arguments = $arguments;
		return $this;
	}
}
