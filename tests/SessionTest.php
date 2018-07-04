<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Rise\Session;
use Rise\Path;

final class SessionTest extends TestCase {
	private $root;

	public function setUp() {
		$sessionConfigContent = <<<PHP
<?php
/**
 * Configurations of session.
 *
 * "options": Options for session_start(). See http://php.net/manual/en/session.configuration.php
 *
 * @var array
 */
return [
	'options' => [
		'name' => 'rise_test_session',
	]
];
PHP;

		$this->root = vfsStream::setup('root', null, [
			'config' => [
				'session.php' => $sessionConfigContent,
			],
		]);
	}

	public function tearDown() {
		unset($_SESSION);
		unset($GLOBALS['SID']);
	}

	public function testStart() {
		$path = $this->createMock(Path::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$session = new Session($path);
		$session->start();

		$this->assertTrue(isset($_SESSION));
	}

	public function testDestroy() {
		$path = $this->createMock(Path::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$session = new Session($path);
		$session->start();
		$session->destroy();

		$this->assertFalse(isset($_SESSION));
	}

	public function testRegenerateId() {
		$path = $this->createMock(Path::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$session = new Session($path);
		$session->start();

		$sidBeforeRegenerate = $GLOBALS['SID'];

		$session->regenerate();

		$sidAfterRegenerate = $GLOBALS['SID'];;

		$this->assertNotSame($sidBeforeRegenerate, $sidAfterRegenerate);
	}

	public function testFlash() {
		$path = $this->createMock(Path::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$session = new Session($path);
		$session->start();

		$this->assertSame([], $_SESSION);

		$session->setFlash('userId', 1);

		$this->assertSame([
			'__nextFlash' => [
				'userId' => 1
			]
		], $_SESSION);

		$this->assertNull($session->getFlash('userId'));

		$session->toNextFlash();

		$this->assertSame(1,$session->getFlash('userId'));

		$session->keepFlash('userId');
		$session->toNextFlash();

		$this->assertSame(1,$session->getFlash('userId'));

		$session->toNextFlash();

		$this->assertNull($session->getFlash('userId'));
	}

	public function testCsrf() {
		$path = $this->createMock(Path::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$session = new Session($path);
		$session->start();

		$this->assertArrayNotHasKey('__csrf', $_SESSION);
		$this->assertFalse($session->validateCsrfToken());

		$token = $session->generateCsrfToken();

		$this->assertSame([
			'__csrf' => $token,
		], $_SESSION);

		$this->assertFalse($session->validateCsrfToken('wrong.token'));
		$this->assertTrue($session->validateCsrfToken($token));
		$this->assertSame($token, $session->getCsrfToken());

		$formKey = $session->getCsrfTokenFormKey();

		$this->assertSame(
			'<input type="hidden" name="' . $formKey . '" value="' . $token . '">',
			$session->generateCsrfHtml()
		);
	}
}
