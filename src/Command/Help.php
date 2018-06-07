<?php
namespace Rise\Command;

use Rise\Command;

class Help extends BaseCommand {
	/**
	 * @var \Rise\Command
	 */
	private $command;

	/**
	 * @var int
	 */
	private $maxPrefixLength = 1;

	/**
	 * @var array
	 */
	private $helpLines = [];

	public function __construct(Command $command) {
		$this->command = $command;
	}

	public function show() {
		echo "Usage:\n\n";
		echo "  php bin/rise COMMAND [COMMAND_ARG...]\n\n";
		echo "Commands:\n\n";
		$this->parseRules($this->command->getRules());
		$this->printHelpLines();
	}

	private function parseRules($rules, $prefix = ' ') {
		if (empty($rules)) {
			return;
		}
		if (is_string($rules)) {
			list (, $description) = array_pad(explode(' ', $rules, 2), 2, null);
			array_push($this->helpLines, [$prefix, $description]);
			$this->maxPrefixLength = max($this->maxPrefixLength, strlen($prefix));
			return;
		}
		foreach ($rules as $key => $ruleset) {
			$this->parseRules($ruleset, "$prefix $key");
		}
	}

	private function printHelpLines() {
		foreach ($this->helpLines as $line) {
			echo str_pad($line[0], $this->maxPrefixLength) . '  ' . $line[1] . "\n";
		}
	}
}
