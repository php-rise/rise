<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use Rise\Request;
use Rise\Request\Upload;
use Rise\Request\Upload\File;

final class RequestTest extends TestCase {
	public function setUp() {
		$_SERVER['REQUEST_URI'] = '/products/15';
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$_GET['page'] = '8';
		$_POST['password'] = 'secret';

		$_POST['input'] = 'from_input';
		$_GET['input'] = 'from_query';
		$_GET['param'] = 'from_query';
		$_GET['query'] = 'from_query';
	}

	public function tearDown() {
		unset($_SERVER['REQUEST_URI']);
		unset($_SERVER['REQUEST_METHOD']);

		unset($_GET['page']);
		unset($_POST['password']);

		unset($_POST['input']);
		unset($_GET['input']);
		unset($_GET['param']);
		unset($_GET['query']);
	}

	public function testRequestPath() {
		$upload = $this->createMock(Upload::class);

		$request = new Request($upload);

		$this->assertSame('/products/15', $request->getRequestPath());

		$request->setRequestPath('/products/16');
		$this->assertSame('/products/16', $request->getRequestPath());
	}

	public function testMethod() {
		$upload = $this->createMock(Upload::class);

		$request = new Request($upload);

		$this->assertSame('GET', $request->getMethod());
		$this->assertTrue($request->isMethod('GET'));
		$this->assertFalse($request->isMethod('POST'));
	}

	public function testParams() {
		$upload = $this->createMock(Upload::class);

		$request = new Request($upload);
		$request->setParams([
			'id' => '11',
			'cid' => '22',
		]);

		$this->assertSame([
			'id' => '11',
			'cid' => '22',
		], $request->getParams());
		$this->assertSame('11', $request->getParam('id'));
		$this->assertSame('22', $request->getParam('cid'));
		$this->assertNull($request->getParam('NotExists'));
	}

	public function testQuery() {
		$upload = $this->createMock(Upload::class);

		$request = new Request($upload);

		$this->assertSame('8', $request->getQuery('page'));
		$this->assertNull($request->getQuery('NotExists'));
	}

	public function testInput() {
		$upload = $this->createMock(Upload::class);

		$request = new Request($upload);

		$this->assertSame('secret', $request->getInput('password'));
		$this->assertNull($request->getInput('NotExists'));
	}

	public function testFile() {
		$upload = $this->createMock(Upload::class);

		$file = new File();

		$upload->expects($this->once())
			->method('getFile')
			->willReturn($file);

		$request = new Request($upload);

		$this->assertInstanceOf(File::class, $request->getFile('file'));
	}

	public function testGetPriority() {
		$upload = $this->createMock(Upload::class);

		$upload->expects($this->once())
			->method('getFile')
			->will($this->returnCallback(function ($key) {
				if ($key === 'input'
					|| $key === 'param'
					|| $key === 'query'
					|| $key === 'file'
				) {
					return $this->createMock(File::class);
				}
			}));

		$request = new Request($upload);
		$request->setParams([
			'input' => 'from_param',
			'param' => 'from_param',
		]);

		$this->assertSame('from_input', $request->get('input'));
		$this->assertSame('from_param', $request->get('param'));
		$this->assertSame('from_query', $request->get('query'));
		$this->assertInstanceOf(File::class, $request->get('file'));
	}
}
