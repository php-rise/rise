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
		$dbnamePattern = '/(dbname=(.+?)(;|$))/';

		foreach ($configs as $key => $config) {
			if (isset($config['init']) && $config['init']) {
				if (isset($config['dsn'])) {
					$originalDsn = $config['dsn'];
					$numOfMatches = preg_match($dbnamePattern, $originalDsn, $matches);

					if ($numOfMatches) {
						$shouldBeTrim = $matches[1];
						$dbname = $matches[2];
						$newDsn = str_replace($shouldBeTrim, '', $originalDsn);

						$config['dsn'] = $newDsn;
						$this->db->setConnectionConfig($key, $config);

						$dbh = $this->db->getConnection($key);
						$sql = "CREATE DATABASE IF NOT EXISTS `$dbname`";
						$dbh->exec($sql);

						echo "SQL:\n";
						echo $sql . "\n\n";

						$config['dsn'] = $originalDsn;
						$this->db->setConnectionConfig($key, $config);
					} else {
						echo "Error: \"dbname\" not set in \"dsn\" in config \"$key\".\n\n";
					}
				} else {
					echo "Error: Cannot find \"dsn\" field in config \"$key\".\n\n";
				}
			}
		}

		$this->db->clearConnections();

		$sql = <<<SQL
CREATE TABLE IF NOT EXISTS `migration` (
	`filename` varchar(255) NOT NULL,
	PRIMARY KEY (`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL;

		$this->db->getConnection()->exec($sql);
		echo "SQL:\n";
		echo $sql . "\n\n";
	}
}
