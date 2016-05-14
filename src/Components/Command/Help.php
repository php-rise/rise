<?php
namespace Rise\Components\Command;

class Help extends BaseCommand {
	public function show() {
		echo "Usage: php bin/rise COMMAND [COMMAND_ARG...]\n";
		echo "\n";
		echo "Create database and the migration table.\n";
		echo "$ php bin/rise database initialize\n";
		echo "\n";
		echo "Create a migration file.\n";
		echo "$ php bin/rise database migration create FILENAME\n";
		echo "\n";
		echo "Migrate changes to database.\n";
		echo "$ php bin/rise database migration migrate\n";
		echo "\n";
		echo "Rollback to previous migration.\n";
		echo "$ php bin/rise database migration rollback\n";
	}
}
