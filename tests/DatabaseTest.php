<?php
namespace Rise\Test;

use PDO;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Rise\Database;
use Rise\Path;

final class DatabaseTest extends TestCase {
	private $root;

	public function setUp() {
		$configContent = <<<PHP
<?php
/**
 * The framework will execute "database.php" instead of this file. Please make
 * sure the configurations are in "database.php".
 *
 * "default": Default connection name.
 *
 * "connections": Configurations of different connections.
 *                Format: [
 *                    '<name>' => [
 *                        'dsn' => '<PDO DSN>', // See http://php.net/manual/en/ref.pdo-mysql.connection.php
 *                        'username' => '<username>',
 *                        'password' => '<password>',
 *                    ]
 *                ]
 *
 * @var array
 */
return [
	'default' => 'test',

	'connections' => [
		'test' => [
			'dsn' => 'mysql:unix_socket=/var/run/mysqld/mysqld.sock;charset=utf8',
			'username' => 'root',
			'password' => '',
		],

		'another' => [
			'dsn' => 'mysql:host=localhost;port=3307',
			'username' => 'root',
			'password' => '',
		],
	]
];
PHP;

		$this->root = vfsStream::setup('root', null, [
			'config' => [
				'database.php' => $configContent,
			]
		]);
	}

	public function testConfig() {
		$path = $this->createMock(Path::class);

		$path->expects($this->any())
			->method('getProjectRootPath')
			->willReturn(vfsStream::url('root'));

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$db = new Database($path);

		$this->assertSame(
			[
				'dsn' => 'mysql:unix_socket=/var/run/mysqld/mysqld.sock;charset=utf8',
				'username' => 'root',
				'password' => '',
			],
			$db->getConnectionConfig()
		);
		$this->assertSame(
			[
				'dsn' => 'mysql:host=localhost;port=3307',
				'username' => 'root',
				'password' => '',
			],
			$db->getConnectionConfig('another')
		);
		$this->assertNull($db->getConnectionConfig('notExists'));

		$db->setConnectionConfig('another', [
			'dsn' => 'mysql:unix_socket=/var/run/mysqld/mysqld.sock;charset=utf8',
			'username' => 'root',
			'password' => '',
		]);

		$this->assertSame(
			[
				'dsn' => 'mysql:unix_socket=/var/run/mysqld/mysqld.sock;charset=utf8',
				'username' => 'root',
				'password' => '',
			],
			$db->getConnectionConfig('another')
		);
	}

	public function testPdo() {
		$path = $this->createMock(Path::class);

		$path->expects($this->any())
			->method('getProjectRootPath')
			->willReturn(vfsStream::url('root'));

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$db = new Database($path);

		$this->assertInstanceOf(PDO::class, $db->getConnection());
	}
}
