<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use Rise\Locale;
use Rise\Test\LocaleTest\Path;
use Rise\Test\LocaleTest\Request;

final class LocaleTest extends TestCase {
	public function testConfig() {
		$path = new Path();
		$request = new Request();
		$locale = new Locale($path, $request);
		$locale->readConfigurations();
		$config = require __DIR__ . '/config/locale.php';

		$this->assertSame($config['locales'], $locale->getLocales());
		$this->assertSame($config['defaultLocaleCode'], $locale->getDefaultLocaleCode());
		$this->assertSame($config['translations'], $locale->getTranslations());
	}

	public function testParseUriWithValidLocale() {
		$path = new Path();
		$request = new Request('/zh/a/long/path');
		$locale = new Locale($path, $request);

		$locale->readConfigurations();
		$locale->parseRequestLocale();

		$this->assertSame('zh', $locale->getCurrentLocaleCode());
		$this->assertSame('/a/long/path', $request->getRequestPath());
	}

	public function testParseUriWithInvalidLocale() {
		$path = new Path();
		$request = new Request('/zh-hk/a/long/path');
		$locale = new Locale($path, $request);

		$locale->readConfigurations();
		$locale->parseRequestLocale();

		$this->assertSame($locale->getDefaultLocaleCode(), $locale->getCurrentLocaleCode());
		$this->assertSame('/zh-hk/a/long/path', $request->getRequestPath());
	}

	public function testParseUriRoot() {
		$path = new Path();
		$request = new Request('/zh');
		$locale = new Locale($path, $request);

		$locale->readConfigurations();
		$locale->parseRequestLocale();

		$this->assertSame('zh', $locale->getCurrentLocaleCode());
		$this->assertSame('/', $request->getRequestPath());
	}

	public function testTranslate() {
		$path = new Path();
		$request = new Request('/zh/a/long/path');
		$locale = new Locale($path, $request);

		$locale->readConfigurations();
		$locale->parseRequestLocale();

		$this->assertSame('神', $locale->translate('oh.my.god'));
	}

	public function testTranslationWhenNotFound() {
		$path = new Path();
		$request = new Request('/zh/a/long/path');
		$locale = new Locale($path, $request);

		$locale->readConfigurations();
		$locale->parseRequestLocale();

		$this->assertSame('many gods', $locale->translate('oh.mine.gods', 'many gods'));
	}

	public function testTranslationForSpecificLocale() {
		$path = new Path();
		$request = new Request('/zh/a/long/path');
		$locale = new Locale($path, $request);

		$locale->readConfigurations();
		$locale->parseRequestLocale();

		$this->assertSame('God', $locale->translate('oh.my.god', 'wrong god', 'en'));
	}
}
