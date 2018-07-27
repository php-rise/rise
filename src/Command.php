<?php
namespace Rise;

use Rise\Container\DynamicFactory;

class Command {
	/**
	 * @var string[]
	 */
	protected $arguments;

	/**
	 * @var array
	 */
	protected $rules = [
		'help' => 'Rise\Command\Help.show Show help',
		'db' => [
			'init' => 'Rise\Command\Database\Initializer.initialize Create database and the migration table',
			'migration' => [
				'create' => 'Rise\CommandDatabase\Migration.create Create a migration file',
			],
			'migrate' => 'Rise\Command\Database\Migrator.migrate Migrate changes to database',
			'rollback' => 'Rise\Command\Database\Migrator.rollback Rollback to previous migration',
		],
	];

	/**
	 * @var \Rise\Path
	 */
	protected $path;

	/**
	 * @var \Rise\Container\DynamicFactory
	 */
	protected $dynamicFactory;

	public function __construct(
		Path $path,
		DynamicFactory $dynamicFactory
	) {
		$this->path = $path;
		$this->dynamicFactory = $dynamicFactory;
	}

	/**
	 * @param string $projectRootPath
	 * @return self
	 */
	public function setProjectRoot($projectRootPath) {
		$this->path->setProjectRootPath($projectRootPath);
		return $this;
	}

	/**
	 * @param string[] $argv
	 * @return self
	 */
	public function setArgv($argv) {
		$argvClone = $argv;
		array_shift($argvClone);
		$this->arguments = $argvClone;
		return $this;
	}

	/**
	 * @return self
	 */
	public function run() {
		$this->readConfig();
		$this->execute($this->arguments);
		return $this;
	}

	/**
	 * @return array
	 */
	public function getRules() {
		return $this->rules;
	}

	/**
	 * @param string[] $arguments
	 * @return self
	 */
	public function execute($arguments = []) {
		list($class, $method, $arguments) = $this->parseArguments($arguments);
		if ($class === null) {
			return $this->warn();
		}

		$component = $this->createComponentInstance($class);
		$component->setArguments($arguments)->{$method}();

		return $this;
	}

	/**
	 * @return self
	 */
	protected function readConfig() {
		$file = $this->path->getConfigPath() . '/command.php';
		if (file_exists($file)) {
			$configurations = require($file);
			if (isset($configurations['rules'])) {
				$this->rules = array_replace_recursive($this->rules, $configurations['rules']);
			}
		}
		return $this;
	}

	/**
	 * @return self
	 */
	protected function warn() {
		echo "Command not found.\n";
		return $this;
	}

	/**
	 * @param string[] $args
	 * @return array|null
	 */
	protected function parseArguments($arguments) {
		if (empty($arguments)) {
			$arguments = ['help'];
		}

		$ruleReference = &$this->rules;
		while ($argument = reset($arguments)) {
			if (isset($ruleReference[$argument])) {
				$ruleReference = &$ruleReference[$argument];
				array_shift($arguments);
			} else {
				break;
			}
		}

		if (is_string($ruleReference)) {
			list ($handler, ) = explode(' ', $ruleReference, 2);
			list($class, $method) = array_pad(explode('.', $handler, 2), 2, null);
			return [$class, $method, $arguments];
		}

		return null;
	}

	/**
	 * @param string $className
	 * @return \Rise\Command\BaseCommand
	 */
	protected function createComponentInstance($className) {
		return $this->dynamicFactory->create($className);
	}
}
