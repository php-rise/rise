<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use Rise\Response;
use Rise\Request;
use Rise\Router\UrlGenerator;

final class ResponseTest extends TestCase {
	/**
	 * @runInSeparateProcess
	 */
	public function testSend() {
		$request = $this->createMock(Request::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$expectedHeaders = [
			'Content-Type: text/html; charset=UTF-8',
		];

		$expectedBody = '';

		$response = new Response($request, $urlGenerator);
		$response->send();

		$outputHeaders = xdebug_get_headers();

		foreach ($expectedHeaders as $expected) {
			$this->assertContains($expected, $outputHeaders);
		}
		$this->expectOutputString($expectedBody);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testBody() {
		$request = $this->createMock(Request::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$expectedBody = '<div>Test body</div>';

		$response = new Response($request, $urlGenerator);
		$response->setBody('<div>Test body</div>');
		$response->send();

		$this->expectOutputString($expectedBody);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testCustomHeaders() {
		$request = $this->createMock(Request::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$expectedHeaders = [
			'Content-Type: text/html; charset=UTF-8',
			'X-Extra-Header: extraHeader',
			'X-Extra-Headers: extraHeader1',
			'X-Extra-Headers: extraHeader2',
		];

		$unexpectedHeaders = [
			'X-Will-Remove: willRemove',
		];

		$response = new Response($request, $urlGenerator);
		$response->setHeader('X-Extra-Header', 'extraHeader');
		$response->addHeader('X-Extra-Headers', 'extraHeader1');
		$response->addHeader('X-Extra-Headers', 'extraHeader2');
		$response->setHeader('X-Will-Remove', 'willRemove');
		$response->unsetHeader('X-Will-Remove');
		$response->send();

		$outputHeaders = xdebug_get_headers();

		foreach ($expectedHeaders as $expected) {
			$this->assertContains($expected, $outputHeaders);
		}
		foreach ($unexpectedHeaders as $unexpected) {
			$this->assertNotContains($unexpected, $outputHeaders);
		}
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testContentType() {
		$request = $this->createMock(Request::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$expectedHeaders = [
			'Content-Type: application/json; charset=UTF-8',
		];

		$response = new Response($request, $urlGenerator);
		$response->setContentType('application/json');
		$response->send();

		$outputHeaders = xdebug_get_headers();

		foreach ($expectedHeaders as $expected) {
			$this->assertContains($expected, $outputHeaders);
		}
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testCharset() {
		$request = $this->createMock(Request::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$expectedHeaders = [
			'Content-Type: text/html; charset=Big5',
		];

		$response = new Response($request, $urlGenerator);
		$response->setCharset('Big5');
		$response->send();

		$outputHeaders = xdebug_get_headers();

		foreach ($expectedHeaders as $expected) {
			$this->assertContains($expected, $outputHeaders);
		}
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testRedirect() {
		$request = $this->createMock(Request::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$expectedHeaders = [
			'Content-Type: text/html; charset=UTF-8',
			'Location: http://www.example.com',
		];

		$expectedBody = <<<HTML
<!DOCTYPE html>
<meta charset="UTF-8">
<meta http-equiv="refresh" content="1;url=http://www.example.com">
<title>Redirecting to http://www.example.com</title>
Redirecting to <a href="http://www.example.com">http://www.example.com</a>
HTML;

		$response = new Response($request, $urlGenerator);
		$response->redirect('http://www.example.com');
		$response->send();

		$outputHeaders = xdebug_get_headers();

		foreach ($expectedHeaders as $expected) {
			$this->assertContains($expected, $outputHeaders);
		}
		$this->expectOutputString($expectedBody);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testRedirectNamedRoute() {
		$request = $this->createMock(Request::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$urlGenerator->expects($this->once())
			->method('generate')
			->willReturn('http://www.example.com/products/15');

		$expectedHeaders = [
			'Content-Type: text/html; charset=UTF-8',
			'Location: http://www.example.com/products/15',
		];

		$expectedBody = <<<HTML
<!DOCTYPE html>
<meta charset="UTF-8">
<meta http-equiv="refresh" content="1;url=http://www.example.com/products/15">
<title>Redirecting to http://www.example.com/products/15</title>
Redirecting to <a href="http://www.example.com/products/15">http://www.example.com/products/15</a>
HTML;

		$response = new Response($request, $urlGenerator);
		$response->redirectRoute('products.show', ['id' => 15]);
		$response->send();

		$outputHeaders = xdebug_get_headers();

		foreach ($expectedHeaders as $expected) {
			$this->assertContains($expected, $outputHeaders);
		}
		$this->expectOutputString($expectedBody);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testHooks() {
		$request = $this->createMock(Request::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$executions = [];

		$beforeSend = function () use (&$executions) {
			array_push($executions, 'beforeSend');
		};
		$afterSend = function () use (&$executions) {
			array_push($executions, 'afterSend');
		};

		$response = new Response($request, $urlGenerator);
		$response->onBeforeSend($beforeSend);
		$response->onAfterSend($afterSend);
		$response->setBody('<div>Test body</div>');
		$response->send();

		$this->expectOutputString('<div>Test body</div>');
		$this->assertSame($executions, ['beforeSend', 'afterSend']);
	}
}
