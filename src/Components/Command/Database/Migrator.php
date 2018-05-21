<?php
namespace Rise\Components\Command\Database;

use DateTime;
use Rise\Services\Path;
use Rise\Services\Database;
use Rise\Components\Command\BaseCommand;

class Migrator extends BaseCommand {
	/**
	 * @var Rise\Services\Path
	 */
	private $path;

	/**
	 * @var Rise\Services\Database
	 */
	private $database;

	public function __construct(Path $path, Database $database) {
		$this->path = $path;
		$this->database = $database;
	}

	public function create() {
		if (!isset($this->arguments[0])) {
			echo "Usage: bin/rise database migration create FILENAME\n";
			return;
		}

		$filename = DateTime::createFromFormat('U.u', microtime(true))
			->format('YmdHisu')
			. '-' . $this->arguments[0] . '.php';
		$className = ucfirst($this->arguments[0]);
		$content = <<<EOD
<?php
class $className {
	public function up() {
	}

	public function down() {
	}
}
EOD;
		if (file_put_contents($this->path->getMigrationsPath() . '/' . $filename, $content) === false) {
			echo "Failed to create migration file.\n";
		} else {
			echo "Created migration file \"$filename\"\n";
		}
	}

	public function migrate() {
		$lastMigration = $this->database->getQueryBuilder()
			->select('filename')
			->from('migration')
			->orderBy('filename', 'DESC')
			->setMaxResults(1)
			->execute()
			->fetch();

		$migrationsPath = $this->path->getMigrationsPath();
		$migrationFiles = scandir($migrationsPath);
		$migrationFiles = array_values(array_diff($migrationFiles, ['.', '..', '.keep']));
		// array_splice($migrationFiles, 0, 2); // Remove "." and ".." directories

		if (empty($migrationFiles)
			|| ($lastMigration !== false && $lastMigration['filename'] === end($migrationFiles))
		) {
			echo "There are no new migrations.\n";
		} else {
			if ($lastMigration !== false) {
				$index = array_search($lastMigration['filename'], $migrationFiles);
				array_splice($migrationFiles, 0, $index + 1);
			}

			foreach ($migrationFiles as $filename) {
				$className = substr($filename, strpos($filename, '-') + 1);
				$className = substr($className, 0, strpos($className, '.'));
				$className = ucfirst($className);
				require $migrationsPath . '/' . $filename;
				$instance = new $className;
				$instance->up();

				$this->database->getQueryBuilder()
					->insert('migration')
					->values([
						'filename' => ':filename',
					])
					->setParameter('filename', $filename)
					->execute();

				echo "Migrated file \"$filename\".\n";
			}
		}
	}

	public function rollback() {
		$lastMigration = $this->database->getQueryBuilder()
			->select('filename')
			->from('migration')
			->orderBy('filename', 'DESC')
			->setMaxResults(1)
			->execute()
			->fetch();

		if ($lastMigration === false) {
			echo "There is no previous migration.\n";
		} else {
			$filename = $lastMigration['filename'];
			$className = substr($filename, strpos($filename, '-') + 1);
			$className = substr($className, 0, strpos($className, '.'));
			$className = ucfirst($className);
			require $this->path->getMigrationsPath() . '/' . $filename;
			$instance = new $className;
			$instance->down();

			$affected = $this->database->getQueryBuilder()
				->delete('migration')
				->where('filename = :filename')
				->setParameter('filename', $filename)
				->execute();

			if ($affected) {
				echo "Rolled back migration file \"$filename\".\n";
			} else {
				echo "Cannot remove the migration record in database. Please check manually.\n";
			}
		}
	}
}
