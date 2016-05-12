<?php
namespace Rise\Components\Command\Database;

use Doctrine\DBAL\Schema\Table;

use Rise\Components\Command\BaseCommand;

class Initializer extends BaseCommand {
	public function initialize() {
		$configurations = [];
		$databases = [];
		foreach (service('database')->getConfigurations() as $connectionName => $configuration) {
			$databases[$connectionName] = $configuration['dbname'];
			$configuration['dbname'] = null;
			$configurations[$connectionName] = $configuration;
		}
		service('database')->setConfigurations($configurations);
		foreach ($databases as $connectionName => $database) {
			service('database')->getConnection($connectionName)
				->getSchemaManager()
				->createDatabase($database);
			echo "Created database \"$database\".\n";
		}

		service('database')->clearConnections()->readConfigurations();

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
		service('database')->getConnection()
			->getSchemaManager()
			->createTable($table);
		echo "Created table \"$tableName\".\n";
	}
}
