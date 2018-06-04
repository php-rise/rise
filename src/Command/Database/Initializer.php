<?php
namespace Rise\Command\Database;

use Doctrine\DBAL\Schema\Table;
use Rise\Database;
use Rise\Command\BaseCommand;

class Initializer extends BaseCommand {
	/**
	 * @var \Rise\Database
	 */
	private $database;

	public function __construct(Database $database) {
		$this->database = $database;
	}

	public function initialize() {
		$configurations = [];
		$databases = [];
		foreach ($this->database->getConfigurations() as $connectionName => $configuration) {
			$databases[$connectionName] = $configuration['dbname'];
			$configuration['dbname'] = null;
			$configurations[$connectionName] = $configuration;
		}
		$this->database->setConfigurations($configurations);
		foreach ($databases as $connectionName => $database) {
			$this->database->getConnection($connectionName)
				->getSchemaManager()
				->createDatabase($database);
			echo "Created database \"$database\".\n";
		}

		$this->database->clearConnections()->readConfigurations();

		$tableName = 'migration';
		$table = new Table($tableName);
		$table->addColumn('id', 'integer', [
			'autoincrement' => true,
			'unsigned' => true,
		]);
		$table->addColumn('filename', 'string', [
			'length' => 255,
			'customSchemaOptions' => [
				'unique' => true,
			],
		]);
		$table->setPrimaryKey(['id']);
		$this->database->getConnection()
			->getSchemaManager()
			->createTable($table);
		echo "Created table \"$tableName\".\n";
	}
}
