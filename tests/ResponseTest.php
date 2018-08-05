<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Rise\Response;
use Rise\Request;
use Rise\Router\UrlGenerator;

final class ResponseTest extends TestCase {
	private $root;

	public function setUp() {
		$this->root = vfsStream::setup('root', null, [
			'file.txt' => 'File content',
		]);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSendEmpty() {
		$request = $this->createMock(Request::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$response = new Response($request, $urlGenerator);
		$response->send();

		$this->assertSame(200, http_response_code());
		$this->expectOutputString('');
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSendString() {
		$request = $this->createMock(Request::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$response = new Response($request, $urlGenerator);
		$response->send('Some text');

		$this->assertSame(200, http_response_code());
		$this->expectOutputString('Some text');
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSendExistingFile() {
		$request = $this->createMock(Request::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$response = new Response($request, $urlGenerator);
		$response->sendFile(vfsStream::url('root/file.txt'));

		$this->assertSame(200, http_response_code());
		$this->expectOutputString('File content');
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSendNotExistingFile() {
		$request = $this->createMock(Request::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$response = new Response($request, $urlGenerator);
		$response->sendFile(vfsStream::url('root/wrong_file.txt'));

		$this->assertSame(404, http_response_code());
		$this->expectOutputString('');
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSendStream() {
		$request = $this->createMock(Request::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$response = new Response($request, $urlGenerator);
		$response->setMode(Response::MODE_STREAM);
		$response->send('First');

		$this->assertSame(200, http_response_code());
		$this->expectOutputString('First');
		$this->assertFalse($response->isSent());

		$response->send(' Second');

		$this->expectOutputString('First Second');
		$this->assertFalse($response->isSent());

		$response->end();

		$this->assertTrue($response->isSent());

		$response->send(' More');

		$this->expectOutputString('First Second');
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testWasSent() {
		$request = $this->createMock(Request::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$response = new Response($request, $urlGenerator);
		$response->wasSent();

		$this->assertTrue($response->isSent());

		$response->setBody('Any body');
		$response->send();

		$this->expectOutputString('');
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
	public function testHeaders() {
		$request = $this->createMock(Request::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$expectedHeaders = [
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
	public function testRedirect() {
		$request = $this->createMock(Request::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$expectedHeaders = [
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

		$this->assertSame(302, http_response_code());
		foreach ($expectedHeaders as $expected) {
			$this->assertContains($expected, $outputHeaders);
		}
		$this->expectOutputString($expectedBody);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testRedirectPermanent() {
		$request = $this->createMock(Request::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$expectedHeaders = [
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
		$response->redirect('http://www.example.com', true);
		$response->send();

		$outputHeaders = xdebug_get_headers();

		$this->assertSame(301, http_response_code());
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

		$this->assertSame(302, http_response_code());
		foreach ($expectedHeaders as $expected) {
			$this->assertContains($expected, $outputHeaders);
		}
		$this->expectOutputString($expectedBody);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testRedirectPermanentNamedRoute() {
		$request = $this->createMock(Request::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$urlGenerator->expects($this->once())
			->method('generate')
			->willReturn('http://www.example.com/products/15');

		$expectedHeaders = [
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
		$response->redirectRoute('products.show', ['id' => 15], true);
		$response->send();

		$outputHeaders = xdebug_get_headers();

		$this->assertSame(301, http_response_code());
		foreach ($expectedHeaders as $expected) {
			$this->assertContains($expected, $outputHeaders);
		}
		$this->expectOutputString($expectedBody);
	}

	public function testContentTypes() {
		$request = $this->createMock(Request::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);

		$response = new Response($request, $urlGenerator);

		$this->assertNull($response->getHeader('Content-Type'));

		$response->asHtml();

		$this->assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));

		$response->asHtml('Big5');

		$this->assertSame(['text/html; charset=Big5'], $response->getHeader('Content-Type'));

		$response->asJson();

		$this->assertSame(['application/json; charset=UTF-8'], $response->getHeader('Content-Type'));

		$response->asJson('Big5');

		$this->assertSame(['application/json; charset=Big5'], $response->getHeader('Content-Type'));
	}
}
