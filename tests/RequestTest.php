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

		$_GET['page'] = '8';
		$_POST['password'] = 'secret';
	}

	public function tearDown() {
		unset($_SERVER['REQUEST_URI']);
		unset($_SERVER['REQUEST_METHOD']);
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);

		unset($_GET['page']);
		unset($_POST['password']);
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

	public function testParam() {
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

		$this->assertSame($params, $request->getParams());
		$this->assertSame('1', $request->getParam('id'));
		$this->assertNull($request->getParam('NotExists'));
	}

	public function testQuery() {
		$upload = $this->createMock(Upload::class);
		$result = $this->createMock(Result::class);

		$request = new Request($upload, $result);

		$this->assertSame('8', $request->getQuery('page'));
		$this->assertNull($request->getQuery('NotExists'));
		$this->assertSame('SomeValue', $request->getQuery('NotExists', 'SomeValue'));
	}

	public function testInput() {
		$upload = $this->createMock(Upload::class);
		$result = $this->createMock(Result::class);

		$request = new Request($upload, $result);

		$this->assertSame('secret', $request->getInput('password'));
		$this->assertNull($request->getInput('NotExists'));
		$this->assertSame('SomeValue', $request->getInput('NotExists', 'SomeValue'));
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
