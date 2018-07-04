<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Rise\Translation;
use Rise\Path;

final class TranslationTest extends TestCase {
	private $root;

	public function setUp() {
		$configContent = <<<EOD
<?php
/**
 * Configurations of translations.
 *
 * "defaultLocale": Optional. Default locale code will be used when the locale code cannot be detected.
 *
 * "translations": Translations.
 *                 Format: [
 *                     '<locale code>' => [
 *                         '<key1>' => '<value1>',
 *                         '<key2>' => '<value2>',
 *                         ...
 *                     ],
 *                     ...
 *                 ]
 *
 * @var array
 */
return [
	'defaultLocale' => 'en',

	'translations' => [
		'en' => [
			'hello' => 'Hello',
		],
		'zh' => [
			'hello' => '你好',
		],
	],
];
EOD;
		$this->root = vfsStream::setup('root', null, [
			'config' => [
				'translation.php' => $configContent
			]
		]);
	}

	public function testDefaultLocale() {
		$path = $this->createMock(Path::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$translation = new Translation($path);

		$this->assertSame('en', $translation->getDefaultLocale());
		$this->assertSame('en', $translation->getLocale());
	}

	public function testHasLocale() {
		$path = $this->createMock(Path::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$translation = new Translation($path);

		$this->assertTrue($translation->hasLocale('en'));
		$this->assertTrue($translation->hasLocale('zh'));
		$this->assertFalse($translation->hasLocale('de'));
	}

	public function testSetLocale() {
		$path = $this->createMock(Path::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$translation = new Translation($path);

		$translation->setLocale('de');

		$this->assertSame('en', $translation->getLocale());

		$translation->setLocale('zh');

		$this->assertSame('zh', $translation->getLocale());
	}

	public function testSetDefaultLocale() {
		$path = $this->createMock(Path::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$translation = new Translation($path);

		$translation->setDefaultLocale('de');

		$this->assertSame('en', $translation->getDefaultLocale());

		$translation->setDefaultLocale('zh');

		$this->assertSame('zh', $translation->getDefaultLocale());
	}

	public function testTranslate() {
		$path = $this->createMock(Path::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$translation = new Translation($path);

		$this->assertSame('Hello', $translation->translate('hello'));
		$this->assertSame('Hello', $translation->translate('hello', 'World'));
		$this->assertSame('World', $translation->translate('world', 'World'));
		$this->assertSame('你好', $translation->translate('hello', '', 'zh'));

		$translation->setLocale('zh');

		$this->assertSame('你好', $translation->translate('hello'));
	}
}
