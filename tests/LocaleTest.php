<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Rise\Locale;
use Rise\Path;
use Rise\Request;

final class LocaleTest extends TestCase {
	private $root;

	public function setUp() {
		$configContent = <<<EOD
<?php
/**
 * Configurations of locale.
 *
 * "locales": Locales enabled in the website.
 *            Format: [
 *                '<locale code>' => [
 *                    'name' => '<locale name>',
 *                ],
 *                ...
 *            ]
 *
 * "defaultLocaleCode": Optional. Default locale code will be used when the locale code cannot be detected from url.
 *
 * "translations": Translations.
 *                 Format: [
 *                     '<locale code>' => [
 *                         '<key1>' => '<value1>',
 *                         '<key2>' => [
 *                             '<nested key>' => '<value2>',
 *                             ...
 *                         ],
 *                         ...
 *                     ],
 *                     ...
 *                 ]
 *
 * @var array
 */
return [
	'defaultLocaleCode' => 'en',

	'locales' => [
		'en' => [
			'name' => 'English',
		],
		'zh' => [
			'name' => '中文',
		],
	],

	'translations' => [
		'en' => [
			'hello' => 'Hello',
			'oh' => [
				'my' => [
					'god' => 'God',
				],
			],
		],
		'zh' => [
			'hello' => '你好',
			'oh' => [
				'my' => [
					'god' => '神',
				],
			],
		],
	],
];
EOD;
		$this->root = vfsStream::setup('root', null, [
			'config' => [
				'locale.php' => $configContent
			]
		]);
	}

	public function testParseUriWithValidLocale() {
		$path = $this->createMock(Path::class);
		$request = $this->createMock(Request::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$request->expects($this->any())
			->method('getRequestUri')
			->willReturn('/zh/a/long/path');

		$request->expects($this->once())
			->method('setRequestPath')
			->with($this->equalTo('/a/long/path'));

		$locale = new Locale($path, $request);
		$locale->parseRequestLocale();

		$this->assertSame('zh', $locale->getCurrentLocaleCode());
	}

	public function testParseUriWithInvalidLocale() {
		$path = $this->createMock(Path::class);
		$request = $this->createMock(Request::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$request->expects($this->any())
			->method('getRequestUri')
			->willReturn('/zh-hk/a/long/path');

		$request->expects($this->once())
			->method('setRequestPath')
			->with($this->equalTo('/zh-hk/a/long/path'));

		$locale = new Locale($path, $request);
		$locale->readConfigurations();
		$locale->parseRequestLocale();

		$this->assertSame($locale->getDefaultLocaleCode(), $locale->getCurrentLocaleCode());
	}

	public function testParseUriRoot() {
		$path = $this->createMock(Path::class);
		$request = $this->createMock(Request::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$request->expects($this->any())
			->method('getRequestUri')
			->willReturn('/zh');

		$request->expects($this->once())
			->method('setRequestPath')
			->with($this->equalTo('/'));

		$locale = new Locale($path, $request);
		$locale->readConfigurations();
		$locale->parseRequestLocale();

		$this->assertSame('zh', $locale->getCurrentLocaleCode());
	}

	public function testTranslate() {
		$path = $this->createMock(Path::class);
		$request = $this->createMock(Request::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$request->expects($this->any())
			->method('getRequestUri')
			->willReturn('/zh/a/long/path');

		$locale = new Locale($path, $request);
		$locale->readConfigurations();
		$locale->parseRequestLocale();

		$this->assertSame('神', $locale->translate('oh.my.god'));
	}

	public function testTranslationWhenNotFound() {
		$path = $this->createMock(Path::class);
		$request = $this->createMock(Request::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$request->expects($this->any())
			->method('getRequestUri')
			->willReturn('/zh/a/long/path');

		$locale = new Locale($path, $request);
		$locale->readConfigurations();
		$locale->parseRequestLocale();

		$this->assertSame('many gods', $locale->translate('oh.mine.gods', 'many gods'));
	}

	public function testTranslationForSpecificLocale() {
		$path = $this->createMock(Path::class);
		$request = $this->createMock(Request::class);

		$path->expects($this->any())
			->method('getConfigPath')
			->willReturn(vfsStream::url('root/config'));

		$request->expects($this->any())
			->method('getRequestUri')
			->willReturn('/zh/a/long/path');

		$locale = new Locale($path, $request);
		$locale->readConfigurations();
		$locale->parseRequestLocale();

		$this->assertSame('God', $locale->translate('oh.my.god', 'wrong god', 'en'));
	}
}
