<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use Rise\Request;
use Rise\Request\Upload;
use Rise\Router\Result;

final class RequestTest extends TestCase {
	public function setUp() {
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['REQUEST_URI'] = '/products/15?buy=1';
	}

	public function tearDown() {
		unset($_SERVER['REQUEST_METHOD']);
		unset($_SERVER['REQUEST_URI']);
	}

	public function testPath() {
		$upload = $this->createMock(Upload::class);
		$result = $this->createMock(Result::class);

		$request = new Request($upload, $result);

		$this->assertSame('/products/15', $request->getPath());

		$request->setPath('/products/16');
		$this->assertSame('/products/16', $request->getPath());
	}

	public function testMethod() {
		$upload = $this->createMock(Upload::class);
		$result = $this->createMock(Result::class);

		$request = new Request($upload, $result);

		$this->assertSame('GET', $request->getMethod());
		$this->assertTrue($request->isMethod('GET'));
		$this->assertFalse($request->isMethod('POST'));
	}

	public function testHeader() {
		$upload = $this->createMock(Upload::class);
		$result = $this->createMock(Result::class);

		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$request = new Request($upload, $result);
		$this->assertSame('XMLHttpRequest', $request->getHeader('X-Requested-With'));
		$this->assertNull($request->getHeader('X-Some-Thing'));

		unset($_SERVER['HTTP_X_REQUESTED_WITH']);
	}

	public function testContentType() {
		$upload = $this->createMock(Upload::class);
		$result = $this->createMock(Result::class);

		$_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
		$request = new Request($upload, $result);
		$this->assertSame('application/x-www-form-urlencoded', $request->getContentType());

		// Check if value is cached
		$_SERVER['CONTENT_TYPE'] = 'application/json';
		$this->assertSame('application/x-www-form-urlencoded', $request->getContentType());

		// Contains charset
		$_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded; charset=UTF-8';
		$request = new Request($upload, $result);
		$this->assertSame('application/x-www-form-urlencoded', $request->getContentType());

		// Contains whitespaces
		$_SERVER['CONTENT_TYPE'] = ' application/x-www-form-urlencoded ; charset=UTF-8';
		$request = new Request($upload, $result);
		$this->assertSame('application/x-www-form-urlencoded', $request->getContentType());

		// No Content-Type header
		unset($_SERVER['CONTENT_TYPE']);
		$request = new Request($upload, $result);
		$this->assertSame('', $request->getContentType());
	}

	public function testCharset() {
		$upload = $this->createMock(Upload::class);
		$result = $this->createMock(Result::class);

		$_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded; charset=UTF-8';
		$request = new Request($upload, $result);
		$this->assertSame('UTF-8', $request->getCharset());

		// Check if value is cached
		$_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded; charset=Big5';
		$this->assertSame('UTF-8', $request->getCharset());

		// Contains whitespaces
		$_SERVER['CONTENT_TYPE'] = ' application/x-www-form-urlencoded ; charset = UTF-8  ';
		$request = new Request($upload, $result);
		$this->assertSame('UTF-8', $request->getCharset());

		// No Content-Type header
		unset($_SERVER['CONTENT_TYPE']);
		$request = new Request($upload, $result);
		$this->assertSame('', $request->getCharset());
	}

	public function testGetQuery() {
		$upload = $this->createMock(Upload::class);
		$result = $this->createMock(Result::class);
		$_GET = [
			'param' => '1'
		];

		$request = new Request($upload, $result);

		$this->assertSame(['param' => '1'], $request->getQuery());

		unset($_GET);
	}

	public function testGetInput() {
		$upload = $this->createMock(Upload::class);
		$result = $this->createMock(Result::class);

		// test POST in application/x-www-form-urlencoded
		$_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST = [
			'param' => '1'
		];
		$request = new Request($upload, $result);
		$this->assertSame(['param' => '1'], $request->getInput());

		// test POST in multipart/form-data
		$_SERVER['CONTENT_TYPE'] = 'multipart/form-data';
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST = [
			'param' => '1'
		];
		$request = new Request($upload, $result);
		$this->assertSame(['param' => '1'], $request->getInput());

		unset($_SERVER['CONTENT_TYPE']);
		unset($_SERVER['REQUEST_METHOD']);
		unset($_POST);
	}

	public function testUrlParam() {
		$upload = $this->createMock(Upload::class);
		$result = $this->createMock(Result::class);

		$params = ['id' => '1'];

		$result->expects($this->once())
			->method('getParams')
			->willReturn($params);

		$result->expects($this->exactly(2))
			->method('getParam')
			->will($this->returnCallback(function ($key) use ($params) {
				if (isset($params[$key])) {
					return $params[$key];
				}
				return null;
			}));

		$request = new Request($upload, $result);

		$this->assertSame($params, $request->getUrlParams());
		$this->assertSame('1', $request->getUrlParam('id'));
		$this->assertNull($request->getUrlParam('NotExists'));
	}

	public function testFiles() {
		$upload = $this->createMock(Upload::class);
		$result = $this->createMock(Result::class);

		$files = [];

		$upload->expects($this->once())
			->method('getFiles')
			->willReturn($files);

		$request = new Request($upload, $result);
		$this->assertSame($files, $request->getFiles());
	}
}
