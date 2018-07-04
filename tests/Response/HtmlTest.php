<?php
namespace Rise\Test\Response;

use Closure;
use PHPUnit\Framework\TestCase;
use Rise\Response\Html;
use Rise\Response;
use Rise\Template;

final class HtmlTest extends TestCase {
	public function testDefaultBehaviour() {
		$response = $this->createMock(Response::class);
		$template = $this->createMock(Template::class);

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
			->method('setBody')
			->with($this->equalTo('<div>Test body</div>'))
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('setContentType')
			->with($this->equalTo('text/html'))
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('setCharset')
			->with($this->equalTo('UTF-8'))
			->will($this->returnSelf());

		$template->expects($this->once())
			->method('render')
			->with($this->equalTo('template/path'), $this->equalTo(['id' => 1]))
			->willReturn('<div>Test body</div>');

		$html = new Html($response, $template);
		$html->render('template/path', ['id' => 1]);
		$html->send();
	}

	public function testChangeCharset() {
		$response = $this->createMock(Response::class);
		$template = $this->createMock(Template::class);

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
			->method('setBody')
			->with($this->equalTo('<div>Test body</div>'))
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('setContentType')
			->with($this->equalTo('text/html'))
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('setCharset')
			->with($this->equalTo('Big5'))
			->will($this->returnSelf());

		$template->expects($this->once())
			->method('render')
			->with($this->equalTo('template/path'))
			->willReturn('<div>Test body</div>');

		$html = new Html($response, $template);
		$html->render('template/path');
		$html->setCharset('Big5');
		$html->send();
	}

	public function testUpdateData() {
		$response = $this->createMock(Response::class);
		$template = $this->createMock(Template::class);

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
			->method('setBody')
			->with($this->equalTo('<div>Test body</div>'))
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('setContentType')
			->with($this->equalTo('text/html'))
			->will($this->returnSelf());

		$response->expects($this->once())
			->method('setCharset')
			->with($this->equalTo('UTF-8'))
			->will($this->returnSelf());

		$template->expects($this->once())
			->method('render')
			->with(
				$this->equalTo('template/path'),
				$this->equalTo([
					'id' => 2,
					'level1' => [
						'level2' => 'Level 2',
						'level2-1' => 'Level 2 - 1 (Modified)',
					],
					'someKey' => 'Some text',
				])
			)
			->willReturn('<div>Test body</div>');

		$html = new Html($response, $template);
		$html->render('template/path', [
			'id' => 1,
			'level1' => [
				'level2' => 'Level 2',
				'level2-1' => 'Level 2 - 1',
			]
		]);
		$html->update(['id' => 2]);
		$html->update([
			'level1' => [
				'level2-1' => 'Level 2 - 1 (Modified)'
			],
			'someKey' => 'Some text',
		], true);
		$html->send();
	}
}
