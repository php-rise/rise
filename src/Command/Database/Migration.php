<?php
namespace Rise\Command\Database;

use DateTime;
use Rise\Command\BaseCommand;
use Rise\Path;

class Migration extends BaseCommand {
	/**
	 * @var \Rise\Path
	 */
	private $path;

	public function __construct(Path $path) {
		$this->path = $path;
	}

	public function create() {
		if (!isset($this->arguments[0])) {
			echo "Usage: bin/rise db migration create FILENAME\n";
			return;
		}

		$classNamePrefix = ucfirst($this->arguments[0]);
		$datetime = DateTime::createFromFormat('U.u', microtime(true))->format('YmdHisu');

		$filename = $datetime . '-' . $classNamePrefix . '.php';
		$className = $classNamePrefix . $datetime;
		$upContent = '';
		$downContent = '';

		if (substr($className, 0, 6) === 'Create') {
			$upContent = <<<SQL
CREATE TABLE `table` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL;

			$downContent = <<<SQL
DROP TABLE `table`;
SQL;
		}

		$content = <<<PHP
<?php
class $className {
	public function up(\PDO \$dbh) {
		return <<<SQL
$upContent
SQL;
	}

	public function down(\PDO \$dbh) {
		return <<<SQL
$downContent
SQL;
	}
}
PHP;

		if (file_put_contents($this->path->getMigrationsPath() . '/' . $filename, $content) === false) {
			echo "Failed to create migration file.\n";
		} else {
			echo "Created migration file \"$filename\"\n";
		}
	}
}
