<?php
namespace Rise\Test\Router;

use PHPUnit\Framework\TestCase;
use Rise\Router\Result;

final class ResultTest extends TestCase {
	public function testStatusCode() {
		$result = new Result();

		$this->assertSame(404, $result->getStatus());

		$result->setHandler(['Handler.handle']);

		$this->assertSame(200, $result->getStatus());

		$result->setHandler(['Handler.handle'], 302);

		$this->assertSame(302, $result->getStatus());
	}

	public function testHandler() {
		$result = new Result();

		$this->assertFalse($result->hasHandler());

		$result->setHandler([]);

		$this->assertFalse($result->hasHandler());

		$result->setHandler(['Handler.handle']);

		$this->assertTrue($result->hasHandler());
		$this->assertSame(['Handler.handle'], $result->getHandler());
	}

	public function testParams() {
		$result = new Result();

		$result->setParams([
			'id' => '11',
			'cid' => '22',
		]);

		$this->assertSame([
			'id' => '11',
			'cid' => '22',
		], $result->getParams());
		$this->assertSame('11', $result->getParam('id'));
		$this->assertSame('22', $result->getParam('cid'));
		$this->assertNull($result->getParam('NotExists'));
		$this->assertSame('SomeValue', $result->getParam('NotExists', 'SomeValue'));
	}
}
