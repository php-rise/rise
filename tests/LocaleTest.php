<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use Rise\Locale;
use Rise\Path;
use Rise\Http\Request;

final class LocaleTest extends TestCase {
	public function testConfig() {
		$path = $this->createMock(Path::class);
		$request = $this->createMock(Request::class);

		$path->expects($this->any())
			->method('getConfigurationsPath')
			->willReturn(__DIR__ . '/config');

		$locale = new Locale($path, $request);
		$locale->readConfigurations();

		$config = require __DIR__ . '/config/locale.php';

		$this->assertSame($config['locales'], $locale->getLocales());
		$this->assertSame($config['defaultLocaleCode'], $locale->getDefaultLocaleCode());
		$this->assertSame($config['translations'], $locale->getTranslations());
	}

	public function testParseUriWithValidLocale() {
		$path = $this->createMock(Path::class);
		$request = $this->createMock(Request::class);

		$path->expects($this->any())
			->method('getConfigurationsPath')
			->willReturn(__DIR__ . '/config');

		$request->expects($this->any())
			->method('getRequestUri')
			->willReturn('/zh/a/long/path');

		$request->expects($this->once())
			->method('setRequestPath')
			->with($this->equalTo('/a/long/path'));

		$locale = new Locale($path, $request);
		$locale->readConfigurations();
		$locale->parseRequestLocale();

		$this->assertSame('zh', $locale->getCurrentLocaleCode());
	}

	public function testParseUriWithInvalidLocale() {
		$path = $this->createMock(Path::class);
		$request = $this->createMock(Request::class);

		$path->expects($this->any())
			->method('getConfigurationsPath')
			->willReturn(__DIR__ . '/config');

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
			->method('getConfigurationsPath')
			->willReturn(__DIR__ . '/config');

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
			->method('getConfigurationsPath')
			->willReturn(__DIR__ . '/config');

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
			->method('getConfigurationsPath')
			->willReturn(__DIR__ . '/config');

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
			->method('getConfigurationsPath')
			->willReturn(__DIR__ . '/config');

		$request->expects($this->any())
			->method('getRequestUri')
			->willReturn('/zh/a/long/path');

		$locale = new Locale($path, $request);
		$locale->readConfigurations();
		$locale->parseRequestLocale();

		$this->assertSame('God', $locale->translate('oh.my.god', 'wrong god', 'en'));
	}
}
