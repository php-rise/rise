<?php
namespace Rise\Test\Middlewares;

use PHPUnit\Framework\TestCase;
use Rise\Middlewares\Locale as LocaleMiddleware;
use Rise\Request;
use Rise\Response;
use Rise\Translation;

final class LocaleTest extends TestCase {
	public function testFoundLocaleFromPath() {
		$request = $this->createMock(Request::class);
		$response = $this->createMock(Response::class);
		$translation = $this->createMock(Translation::class);
		$executedNext = false;

		$next = function () use (&$executedNext) {
			$executedNext = true;
		};

		$request->expects($this->once())
			->method('getUrlParam')
			->with($this->equalTo('locale'))
			->willReturn('en');

		$translation->expects($this->once())
			->method('hasLocale')
			->with($this->equalTo('en'))
			->willReturn(true);

		$translation->expects($this->once())
			->method('setLocale')
			->with($this->equalTo('en'));

		$middleware = new LocaleMiddleware($request, $response, $translation);

		$middleware->extractFromPath($next);

		$this->assertTrue($executedNext);
	}

	public function testNotFoundLocaleFromPath() {
		$request = $this->createMock(Request::class);
		$response = $this->createMock(Response::class);
		$translation = $this->createMock(Translation::class);
		$executedNext = false;

		$next = function () use (&$executedNext) {
			$executedNext = true;
		};

		$request->expects($this->once())
			->method('getUrlParam')
			->with($this->equalTo('locale'))
			->willReturn('en');

		$translation->expects($this->once())
			->method('hasLocale')
			->with($this->equalTo('en'))
			->willReturn(false);

		$translation->expects($this->never())
			->method('setLocale');

		$middleware = new LocaleMiddleware($request, $response, $translation);

		$middleware->extractFromPath($next);

		$this->assertFalse($executedNext);
	}

	public function testFoundLocaleFromTld() {
		$request = $this->createMock(Request::class);
		$response = $this->createMock(Response::class);
		$translation = $this->createMock(Translation::class);
		$executedNext = false;

		$next = function () use (&$executedNext) {
			$executedNext = true;
		};

		$request->expects($this->once())
			->method('getHost')
			->willReturn('www.example.com.hk');

		$translation->expects($this->once())
			->method('hasLocale')
			->with($this->equalTo('hk'))
			->willReturn(true);

		$translation->expects($this->once())
			->method('setLocale')
			->with($this->equalTo('hk'));

		$middleware = new LocaleMiddleware($request, $response, $translation);

		$middleware->extractFromTld($next);

		$this->assertTrue($executedNext);
	}

	public function testNotFoundLocaleFromTld() {
		$request = $this->createMock(Request::class);
		$response = $this->createMock(Response::class);
		$translation = $this->createMock(Translation::class);
		$executedNext = false;

		$next = function () use (&$executedNext) {
			$executedNext = true;
		};

		$request->expects($this->once())
			->method('getHost')
			->willReturn('www.example.com.hk');

		$translation->expects($this->once())
			->method('hasLocale')
			->with($this->equalTo('hk'))
			->willReturn(false);

		$translation->expects($this->never())
			->method('setLocale');

		$middleware = new LocaleMiddleware($request, $response, $translation);

		$middleware->extractFromTld($next);

		$this->assertFalse($executedNext);
	}

	public function testFoundLocaleFromSubdomain() {
		$request = $this->createMock(Request::class);
		$response = $this->createMock(Response::class);
		$translation = $this->createMock(Translation::class);
		$executedNext = false;

		$next = function () use (&$executedNext) {
			$executedNext = true;
		};

		$request->expects($this->once())
			->method('getHost')
			->willReturn('hk.example.com');

		$translation->expects($this->once())
			->method('hasLocale')
			->with($this->equalTo('hk'))
			->willReturn(true);

		$translation->expects($this->once())
			->method('setLocale')
			->with($this->equalTo('hk'));

		$middleware = new LocaleMiddleware($request, $response, $translation);

		$middleware->extractFromSubdomain($next);

		$this->assertTrue($executedNext);
	}

	public function testNotFoundLocaleFromSubdomain() {
		$request = $this->createMock(Request::class);
		$response = $this->createMock(Response::class);
		$translation = $this->createMock(Translation::class);
		$executedNext = false;

		$next = function () use (&$executedNext) {
			$executedNext = true;
		};

		$request->expects($this->once())
			->method('getHost')
			->willReturn('hk.example.com');

		$translation->expects($this->once())
			->method('hasLocale')
			->with($this->equalTo('hk'))
			->willReturn(false);

		$translation->expects($this->never())
			->method('setLocale');

		$middleware = new LocaleMiddleware($request, $response, $translation);

		$middleware->extractFromSubdomain($next);

		$this->assertFalse($executedNext);
	}
}
