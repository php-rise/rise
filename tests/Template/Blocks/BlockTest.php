<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Rise\Template\Blocks\Block;
use Rise\Template;
use Rise\Path;
use Rise\Router\UrlGenerator;
use Rise\Session;

final class BlockTest extends TestCase {
	private $root;

	public function setUp() {
		$simpleBlockContent = <<<'PHTML'
Simple block
PHTML;

		$includeBlockContent = <<<'PHTML'
<div><?=$this->include('blocks/partials/modal')?></div>
PHTML;

		$includeParamBlockContent = <<<'PHTML'
<div><?=$this->include('blocks/partials/modal', ['someKey' => 'Something'])?></div>
PHTML;

		$extendBlockContent = <<<'PHTML'
<?php $this->extend('layouts/main') ?>
Extend block
PHTML;

		$extendParamBlockContent = <<<'PHTML'
<?php $this->extend('layouts/main', ['someKey' => 'Something']) ?>
Extend block
PHTML;

		$extendNonArrayParamBlockContent = <<<'PHTML'
<?php $this->extend('layouts/main', 1) ?>
Extend block
PHTML;

		$extendParamNameBlockContent = <<<'PHTML'
<?php $this->extend('layouts/main', [], 'content') ?>
Extend block
PHTML;

		$extendEmptyStringParamNameBlockContent = <<<'PHTML'
<?php $this->extend('layouts/main', [], '') ?>
Extend block
PHTML;

		$extendNonStringParamNameBlockContent = <<<'PHTML'
<?php $this->extend('layouts/main', [], 1) ?>
Extend block
PHTML;

		$this->root = vfsStream::setup('root', null, [
			'templates' => [
				'blocks' => [
					'simple.phtml' => $simpleBlockContent,
					'include.phtml' => $includeBlockContent,
					'include-param.phtml' => $includeParamBlockContent,
					'extend.phtml' => $extendBlockContent,
					'extend-param.phtml' => $extendParamBlockContent,
					'extend-non-array-param.phtml' => $extendNonArrayParamBlockContent,
					'extend-param-name.phtml' => $extendParamNameBlockContent,
					'extend-empty-string-param-name.phtml' => $extendEmptyStringParamNameBlockContent,
					'extend-non-string-param-name.phtml' => $extendNonStringParamNameBlockContent,
				],
			]
		]);
	}

	public function testSimpleBlock() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);

		$path->expects($this->any())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$block = new Block($path, $template, $urlGenerator, $session);

		$block->setTemplate('blocks/simple');

		$this->assertSame('Simple block', $block->render());
	}

	public function testIncludeBlock() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);

		$path->expects($this->any())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->once())
			->method('render')
			->with($this->equalTo('blocks/partials/modal'));

		$block = new Block($path, $template, $urlGenerator, $session);

		$block->setTemplate('blocks/include');
		$block->render();
	}

	public function testIncludeParamBlock() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);

		$path->expects($this->any())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->once())
			->method('render')
			->with(
				$this->equalTo('blocks/partials/modal'),
				$this->equalTo(['someKey' => 'Something'])
			);

		$block = new Block($path, $template, $urlGenerator, $session);

		$block->setTemplate('blocks/include-param');
		$block->render();
	}

	public function testExendBlock() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);

		$path->expects($this->any())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->once())
			->method('render')
			->with(
				$this->equalTo('layouts/main'),
				$this->equalTo(['body' => 'Extend block'])
			);

		$block = new Block($path, $template, $urlGenerator, $session);

		$block->setTemplate('blocks/extend');
		$block->render();
	}

	public function testExendParamBlock() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);

		$path->expects($this->any())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->once())
			->method('render')
			->with(
				$this->equalTo('layouts/main'),
				$this->equalTo(['body' => 'Extend block', 'someKey' => 'Something'])
			);

		$block = new Block($path, $template, $urlGenerator, $session);

		$block->setTemplate('blocks/extend-param');
		$block->render();
	}

	public function testExendNonArrayParamBlock() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);

		$path->expects($this->any())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->once())
			->method('render')
			->with(
				$this->equalTo('layouts/main'),
				$this->equalTo(['body' => 'Extend block'])
			);

		$block = new Block($path, $template, $urlGenerator, $session);

		$block->setTemplate('blocks/extend-non-array-param');
		$block->render();
	}

	public function testExendParamNameBlock() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);

		$path->expects($this->any())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->once())
			->method('render')
			->with(
				$this->equalTo('layouts/main'),
				$this->equalTo(['content' => 'Extend block'])
			);

		$block = new Block($path, $template, $urlGenerator, $session);

		$block->setTemplate('blocks/extend-param-name');
		$block->render();
	}

	public function testExendEmptyStringParamNameBlock() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);

		$path->expects($this->any())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->once())
			->method('render')
			->with(
				$this->equalTo('layouts/main'),
				$this->equalTo(['body' => 'Extend block'])
			);

		$block = new Block($path, $template, $urlGenerator, $session);

		$block->setTemplate('blocks/extend-empty-string-param-name');
		$block->render();
	}

	public function testExendNonStringParamNameBlock() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);

		$path->expects($this->any())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->once())
			->method('render')
			->with(
				$this->equalTo('layouts/main'),
				$this->equalTo(['body' => 'Extend block'])
			);

		$block = new Block($path, $template, $urlGenerator, $session);

		$block->setTemplate('blocks/extend-non-string-param-name');
		$block->render();
	}

	public function testUrlHelper() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);

		$urlGenerator->expects($this->once())
			->method('generate')
			->with(
				$this->equalTo('product.show'),
				$this->equalTo(['id' => 1])
			);

		$block = new Block($path, $template, $urlGenerator, $session);

		$block->url('product.show', ['id' => 1]);
	}
}
