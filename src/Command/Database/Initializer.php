<?php
namespace Rise\Command\Database;

use Rise\Database;
use Rise\Command\BaseCommand;

class Initializer extends BaseCommand {
	/**
	 * @var \Rise\Database
	 */
	private $db;

	public function __construct(Database $db) {
		$this->db = $db;
	}

	public function initialize() {
		$configs = $this->db->getConnectionConfigs();
		$keys = array_keys($configs);
		$keys = array_filter($keys, function ($key) {
			return substr($key, 0, 5) === '_init';
		});

		foreach ($keys as $key) {
			$config = $configs[$key];

			if (isset($config['dbname'])) {
				$dbname = $config['dbname'];
				$dbh = $this->db->getConnection($key);
				$dbh->exec("CREATE DATABASE `$dbname`");
				echo "Created database \"$dbname\".\n";
			} else {
				echo "\"dbname\" not set in config \"$key\"";
			}
		}

		$this->db->clearConnections();

		$sql = <<<SQL
CREATE TABLE `migration` (
	`filename` varchar(255) NOT NULL,
	PRIMARY KEY (`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL;

		$this->db->getConnection()->exec($sql);
		echo "Created table \"migration\".\n";
	}
}
