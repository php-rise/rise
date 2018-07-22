<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use Rise\Request;
use Rise\Request\Upload;
use Rise\Router\Result;

final class RequestTest extends TestCase {
	public function setUp() {
		$_SERVER['REQUEST_URI'] = '/products/15?buy=1';
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
	}

	public function tearDown() {
		unset($_SERVER['REQUEST_URI']);
		unset($_SERVER['REQUEST_METHOD']);
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);
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

		$request = new Request($upload, $result);

		$this->assertSame('XMLHttpRequest', $request->getHeader('X-Requested-With'));
		$this->assertNull($request->getHeader('X-Some-Thing'));
	}

	public function testGetParams() {
		$upload = $this->createMock(Upload::class);
		$result = $this->createMock(Result::class);
		$_GET = [
			'param' => '1'
		];

		$request = new Request($upload, $result);

		$this->assertSame(['param' => '1'], $request->getGetParams());

		unset($_GET);
	}

	public function testPostParams() {
		$upload = $this->createMock(Upload::class);
		$result = $this->createMock(Result::class);
		$_POST = [
			'param' => '1'
		];

		$request = new Request($upload, $result);

		$this->assertSame(['param' => '1'], $request->getPostParams());

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
