<?php
namespace Rise\Command\Database;

use PDOException;
use Rise\Command\BaseCommand;
use Rise\Path;
use Rise\Database;

class Migrator extends BaseCommand {
	/**
	 * @var \Rise\Path
	 */
	private $path;

	/**
	 * @var \Rise\Database
	 */
	private $db;

	public function __construct(Path $path, Database $db) {
		$this->path = $path;
		$this->db = $db;
	}

	public function migrate() {
		$dbh = $this->db->getConnection();

		$sql = <<<SQL
SELECT * FROM `migration`
ORDER BY `filename` DESC
LIMIT 1
SQL;

		$sth = $dbh->query($sql);
		$lastMigration = $sth->fetch();

		$migrationsPath = $this->path->getMigrationsPath();
		$migrationFiles = scandir($migrationsPath);
		$migrationFiles = array_values(array_diff($migrationFiles, ['.', '..', '.keep']));
		// array_splice($migrationFiles, 0, 2); // Remove "." and ".." directories

		if (empty($migrationFiles)
			|| ($lastMigration !== false && $lastMigration['filename'] === end($migrationFiles))
		) {
			echo "There is no new migration.\n";
		} else {
			if ($lastMigration !== false) {
				$index = array_search($lastMigration['filename'], $migrationFiles);
				array_splice($migrationFiles, 0, $index + 1);
			}

			foreach ($migrationFiles as $filename) {
				require $migrationsPath . '/' . $filename;
				$className = $this->getClassNameFromFilename($filename);
				$instance = new $className;

				try {
					$dbh->beginTransaction();
					$sql = $instance->up($dbh);
					if (is_string($sql)) {
						$sth = $dbh->prepare($sql);
						$sth->execute();
					}
					$sql = <<<SQL
INSERT INTO `migration` (`filename`)
VALUES (:filename)
SQL;
					$sth = $dbh->prepare($sql);
					$sth->execute([
						'filename' => $filename
					]);
					$dbh->commit();
					echo "Migrated file \"$filename\".\n";
				} catch (PDOException $e) {
					$dbh->rollback();
					echo "Error in file \"$filename\".\n";
					throw $e;
				}
			}
		}
	}

	public function rollback() {
		$dbh = $this->db->getConnection();

		$sql = <<<SQL
SELECT * FROM `migration`
ORDER BY `filename` DESC
LIMIT 1
SQL;

		$sth = $dbh->query($sql);
		$lastMigration = $sth->fetch();

		if ($lastMigration === false) {
			echo "There is no previous migration.\n";
		} else {
			$filename = $lastMigration['filename'];
			require $this->path->getMigrationsPath() . '/' . $filename;
			$className = $this->getClassNameFromFilename($filename);
			$instance = new $className;

			try {
				$dbh->beginTransaction();
				$sql = $instance->down($dbh);
				if (is_string($sql)) {
					$sth = $dbh->prepare($sql);
					$sth->execute();
				}
				$sql = <<<SQL
DELETE FROM `migration`
WHERE `filename` = :filename
SQL;
				$sth = $dbh->prepare($sql);
				$sth->execute([
					'filename' => $filename
				]);
				$affected = $sth->rowCount();
				if ($affected) {
					echo "Rolled back migration file \"$filename\".\n";
				} else {
					echo "Cannot remove the migration record $filename in database. Please check manually.\n";
				}
				$dbh->commit();
			} catch (PDOException $e) {
				$dbh->rollback();
				echo "Error in file \"$filename\".\n";
				throw $e;
			}
		}
	}

	private function getClassNameFromFilename($filename) {
		list ($datetime, $classNamePrefix) = explode('-', pathinfo($filename, PATHINFO_FILENAME));
		return ucfirst($classNamePrefix) . $datetime;
	}
}
