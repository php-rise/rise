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
		'help' => 'Help.show',
		'database' => [
			'initialize' => 'Database\Initializer.initialize',
			'migration' => [
				'create' => 'Database\Migrator.create',
				'migrate' => 'Database\Migrator.migrate',
				'rollback' => 'Database\Migrator.rollback',
			],
		],
	];

	/**
	 * @var string[]
	 */
	protected $namespaces = ['\Rise\Command'];

	/**
	 * @var \Rise\Initializer
	 */
	protected $initializer;

	/**
	 * @var \Rise\Path
	 */
	protected $path;

	/**
	 * @var \Rise\Database
	 */
	protected $database;

	/**
	 * @var \Rise\Container\DynamicFactory
	 */
	protected $dynamicFactory;

	public function __construct(
		Initializer $initializer,
		Path $path,
		Database $database,
		DynamicFactory $dynamicFactory
	) {
		$this->initializer = $initializer;
		$this->path = $path;
		$this->database = $database;
		$this->dynamicFactory = $dynamicFactory;
	}

	/**
	 * @param string $projectRootPath
	 * @return self
	 */
	public function setProjectRootPath($projectRootPath) {
		$this->initializer->setProjectRootPath($projectRootPath);
		return $this;
	}

	/**
	 * @param string[] $argv
	 * @return self
	 */
	public function setArguments($arguments) {
		$this->arguments = $arguments;
		return $this;
	}

	/**
	 * @param string[] $argv
	 * @return self
	 */
	public function setArgv($argv) {
		$argvClone = $argv;
		array_shift($argvClone);
		$this->setArguments($argvClone);
		return $this;
	}

	/**
	 * @return self
	 */
	public function run() {
		$this->readConfigurations();
		$this->database->readConfigurations();
		$this->execute($this->arguments);
		return $this;
	}

	/**
	 * Add namespace for searching.
	 *
	 * @param string $namespace
	 * @return self
	 */
	public function addNamespace($namespace = '') {
		if ($namespace) {
			$this->namespaces[] = $namespace;
		}
		return $this;
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
		if ($component === null) {
			return $this->warn();
		}

		$component->setArguments($arguments)->{$method}();

		return $this;
	}

	/**
	 * @return self
	 */
	protected function readConfigurations() {
		$file = $this->path->getConfigurationsPath() . '/command.php';
		if (file_exists($file)) {
			$configurations = require($file);
			if (isset($configurations['namespaces'])) {
				$this->namespaces = array_merge($this->namespaces, (array)$configurations['namespaces']);
			}
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
			list($class, $method) = array_pad(explode('.', $ruleReference, 2), 2, null);
			return [$class, $method, $arguments];
		}

		return null;
	}

	/**
	 * @param string $partClassName
	 * @return \Rise\Command\BaseCommand|null
	 */
	protected function createComponentInstance($partClassName) {
		$namespace = end($this->namespaces);
		do {
			$class = $namespace . '\\' . $partClassName;
			if (class_exists($class)) {
				break;
			}
			$class = null;
		} while ($namespace = prev($this->namespaces));

		if ($class === null) {
			return null;
		}

		return $this->dynamicFactory->create($class);
	}
}