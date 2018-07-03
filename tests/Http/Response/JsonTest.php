<?php
namespace Rise\Test\Http\Response;

use Closure;
use PHPUnit\Framework\TestCase;
use Rise\Http\Response\Json;
use Rise\Http\Response;

final class JsonTest extends TestCase {
	public function test() {
		$response = $this->createMock(Response::class);

		$beforeSend = '';

		$response->expects($this->once())
			->method('onBeforeSend')
			->with($this->callback(function ($cb) use (&$beforeSend) {
				$beforeSend = $cb;
				return $cb instanceof Closure;
			}));

		$response->expects($this->once())
			->method('send')
			->will($this->returnCallback(function () use (&$beforeSend) {
				$beforeSend();
				return null;
			}));

		$response->expects($this->once())
			->method('setContentType')
			->with($this->equalTo('application/json'))
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('setCharset')
			->with($this->equalTo('UTF-8'))
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('setBody')
			->with($this->equalTo(json_encode('Test string')))
			->will($this->returnSelf());

		$json = new Json($response);
		$json->data('Test string');
		$json->send();
	}

	public function testChangeCharset() {
		$response = $this->createMock(Response::class);

		$beforeSend = '';

		$response->expects($this->once())
			->method('onBeforeSend')
			->with($this->callback(function ($cb) use (&$beforeSend) {
				$beforeSend = $cb;
				return $cb instanceof Closure;
			}));

		$response->expects($this->once())
			->method('send')
			->will($this->returnCallback(function () use (&$beforeSend) {
				$beforeSend();
				return null;
			}));

		$response->expects($this->once())
			->method('setContentType')
			->with($this->equalTo('application/json'))
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('setCharset')
			->with($this->equalTo('Big5'))
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('setBody')
			->with($this->equalTo(json_encode('Test string')))
			->will($this->returnSelf());

		$json = new Json($response);
		$json->data('Test string');
		$json->setCharset('Big5');
		$json->send();
	}

	public function testUpdateArrayData() {
		$response = $this->createMock(Response::class);

		$beforeSend = '';

		$response->expects($this->once())
			->method('onBeforeSend')
			->with($this->callback(function ($cb) use (&$beforeSend) {
				$beforeSend = $cb;
				return $cb instanceof Closure;
			}));

		$response->expects($this->once())
			->method('send')
			->will($this->returnCallback(function () use (&$beforeSend) {
				$beforeSend();
				return null;
			}));

		$response->expects($this->once())
			->method('setContentType')
			->with($this->equalTo('application/json'))
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('setCharset')
			->with($this->equalTo('UTF-8'))
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('setBody')
			->with($this->equalTo(json_encode([
					'id' => 2,
					'level1' => [
						'level2' => 'Level 2',
						'level2-1' => 'Level 2 - 1 (Modified)',
					],
					'someKey' => 'Some text',
				])))
			->will($this->returnSelf());

		$json = new Json($response);
		$json->data([
			'id' => 1,
			'level1' => [
				'level2' => 'Level 2',
				'level2-1' => 'Level 2 - 1',
			]
		]);
		$json->update(['id' => 2]);
		$json->update([
			'level1' => [
				'level2-1' => 'Level 2 - 1 (Modified)'
			],
			'someKey' => 'Some text',
		], true);
		$json->send();
	}

	public function testUpdateNonArrayData() {
		$response = $this->createMock(Response::class);

		$beforeSend = '';

		$response->expects($this->once())
			->method('onBeforeSend')
			->with($this->callback(function ($cb) use (&$beforeSend) {
				$beforeSend = $cb;
				return $cb instanceof Closure;
			}));

		$response->expects($this->once())
			->method('send')
			->will($this->returnCallback(function () use (&$beforeSend) {
				$beforeSend();
				return null;
			}));

		$response->expects($this->once())
			->method('setContentType')
			->with($this->equalTo('application/json'))
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('setCharset')
			->with($this->equalTo('UTF-8'))
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('setBody')
			->with($this->equalTo(json_encode('Another string')))
			->will($this->returnSelf());

		$json = new Json($response);
		$json->data('Test string');
		$json->update('Another string');
		$json->send();
	}
}
