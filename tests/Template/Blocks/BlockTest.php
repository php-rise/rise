<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Rise\Template\Blocks\Block;
use Rise\Template\Blocks\NotFoundException;
use Rise\Template\Blocks\BlockException;
use Rise\Template;
use Rise\Path;
use Rise\Router\UrlGenerator;
use Rise\Session;
use Rise\Translation;

final class BlockTest extends TestCase {
	private $root;

	public function setUp() {
		$emptyBlockContent = '';

		$simpleBlockContent = <<<'PHTML'
Simple block
PHTML;

		$layoutBlockContent = '';

		$this->root = vfsStream::setup('root', null, [
			'templates' => [
				'blocks' => [
					'empty.phtml' => $emptyBlockContent,
					'simple.phtml' => $simpleBlockContent,
					'layout.phtml' => $layoutBlockContent,
				],
			]
		]);
	}

	public function testSetTemplateAndRender() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);
		$translation = $this->createMock(Translation::class);

		$path->expects($this->once())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$block = new Block($path, $template, $urlGenerator, $session, $translation);
		$block->setTemplate('blocks/simple');
		$this->assertSame('Simple block', $block->render());
	}

	public function testTemplateNotFound() {
		$this->expectException(NotFoundException::class);

		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);
		$translation = $this->createMock(Translation::class);

		$path->expects($this->once())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$block = new Block($path, $template, $urlGenerator, $session, $translation);
		$block->setTemplate('blocks/not.found');
	}

	public function testInclude() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);
		$translation = $this->createMock(Translation::class);

		$path->expects($this->atLeastOnce())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->once())
			->method('render')
			->with($this->equalTo(vfsStream::url('root/templates/blocks/simple.phtml')));

		$block = new Block($path, $template, $urlGenerator, $session, $translation);
		$block->setTemplate('blocks/empty');
		$block->include('blocks/simple');
	}

	public function testIncludeRelative() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);
		$translation = $this->createMock(Translation::class);

		$path->expects($this->atLeastOnce())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->once())
			->method('render')
			->with($this->equalTo(vfsStream::url('root/templates/blocks/simple.phtml')));

		$block = new Block($path, $template, $urlGenerator, $session, $translation);
		$block->setTemplate('blocks/empty');
		$block->include('./simple');
	}

	public function testIncludeRelativeParent() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);
		$translation = $this->createMock(Translation::class);

		$path->expects($this->atLeastOnce())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->once())
			->method('render')
			->with($this->equalTo(vfsStream::url('root/templates/blocks/simple.phtml')));

		$block = new Block($path, $template, $urlGenerator, $session, $translation);
		$block->setTemplate('blocks/empty');
		$block->include('../blocks/simple');
	}

	public function testIncludeParamBlock() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);
		$translation = $this->createMock(Translation::class);

		$path->expects($this->atLeastOnce())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->once())
			->method('render')
			->with(
				$this->equalTo(vfsStream::url('root/templates/blocks/simple.phtml')),
				$this->equalTo(['someKey' => 'Something'])
			);

		$block = new Block($path, $template, $urlGenerator, $session, $translation);
		$block->setTemplate('blocks/empty');
		$block->include('blocks/simple', ['someKey' => 'Something']);
	}

	public function testIncludeNotFound() {
		$this->expectException(NotFoundException::class);

		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);
		$translation = $this->createMock(Translation::class);

		$path->expects($this->atLeastOnce())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->never())
			->method('render');

		$block = new Block($path, $template, $urlGenerator, $session, $translation);
		$block->setTemplate('blocks/empty');
		$block->include('blocks/not.found');
	}

	public function testExend() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);
		$translation = $this->createMock(Translation::class);

		$path->expects($this->atLeastOnce())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->once())
			->method('render')
			->with(
				$this->equalTo(vfsStream::url('root/templates/blocks/layout.phtml')),
				$this->equalTo(['body' => ''])
			);

		$block = new Block($path, $template, $urlGenerator, $session, $translation);
		$block->setTemplate('blocks/empty');
		$block->extend('blocks/layout');
		$block->render();
	}

	public function testExendRelative() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);
		$translation = $this->createMock(Translation::class);

		$path->expects($this->atLeastOnce())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->once())
			->method('render')
			->with(
				$this->equalTo(vfsStream::url('root/templates/blocks/layout.phtml')),
				$this->equalTo(['body' => ''])
			);

		$block = new Block($path, $template, $urlGenerator, $session, $translation);
		$block->setTemplate('blocks/empty');
		$block->extend('./layout');
		$block->render();
	}

	public function testExendRelativeParent() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);
		$translation = $this->createMock(Translation::class);

		$path->expects($this->atLeastOnce())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->once())
			->method('render')
			->with(
				$this->equalTo(vfsStream::url('root/templates/blocks/layout.phtml')),
				$this->equalTo(['body' => ''])
			);

		$block = new Block($path, $template, $urlGenerator, $session, $translation);
		$block->setTemplate('blocks/empty');
		$block->extend('../blocks/layout');
		$block->render();
	}

	public function testExendParam() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);
		$translation = $this->createMock(Translation::class);

		$path->expects($this->atLeastOnce())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->once())
			->method('render')
			->with(
				$this->equalTo(vfsStream::url('root/templates/blocks/layout.phtml')),
				$this->equalTo(['body' => '', 'someKey' => 'Something'])
			);

		$block = new Block($path, $template, $urlGenerator, $session, $translation);
		$block->setTemplate('blocks/empty');
		$block->extend('blocks/layout', ['someKey' => 'Something']);
		$block->render();
	}

	public function testExendNonArrayParam() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);
		$translation = $this->createMock(Translation::class);

		$path->expects($this->atLeastOnce())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->once())
			->method('render')
			->with(
				$this->equalTo(vfsStream::url('root/templates/blocks/layout.phtml')),
				$this->equalTo(['body' => ''])
			);

		$block = new Block($path, $template, $urlGenerator, $session, $translation);
		$block->setTemplate('blocks/empty');
		$block->extend('blocks/layout', 1);
		$block->render();
	}

	public function testExendParamNameBlock() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);
		$translation = $this->createMock(Translation::class);

		$path->expects($this->atLeastOnce())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->once())
			->method('render')
			->with(
				$this->equalTo(vfsStream::url('root/templates/blocks/layout.phtml')),
				$this->equalTo(['content' => ''])
			);

		$block = new Block($path, $template, $urlGenerator, $session, $translation);
		$block->setTemplate('blocks/empty');
		$block->extend('blocks/layout', [], 'content');
		$block->render();
	}

	public function testExendEmptyStringParamNameBlock() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);
		$translation = $this->createMock(Translation::class);

		$path->expects($this->atLeastOnce())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->once())
			->method('render')
			->with(
				$this->equalTo(vfsStream::url('root/templates/blocks/layout.phtml')),
				$this->equalTo(['body' => ''])
			);

		$block = new Block($path, $template, $urlGenerator, $session, $translation);
		$block->setTemplate('blocks/empty');
		$block->extend('blocks/layout', [], '');
		$block->render();
	}

	public function testExendNonStringParamNameBlock() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);
		$translation = $this->createMock(Translation::class);

		$path->expects($this->atLeastOnce())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->once())
			->method('render')
			->with(
				$this->equalTo(vfsStream::url('root/templates/blocks/layout.phtml')),
				$this->equalTo(['body' => ''])
			);

		$block = new Block($path, $template, $urlGenerator, $session, $translation);
		$block->setTemplate('blocks/empty');
		$block->extend('blocks/layout', [], 1);
		$block->render();
	}

	public function testExendNotFound() {
		$this->expectException(NotFoundException::class);

		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);
		$translation = $this->createMock(Translation::class);

		$path->expects($this->atLeastOnce())
			->method('getTemplatesPath')
			->willReturn(vfsStream::url('root/templates'));

		$template->expects($this->never())
			->method('render');

		$block = new Block($path, $template, $urlGenerator, $session, $translation);
		$block->setTemplate('blocks/empty');
		$block->extend('blocks/not.found');
	}

	public function testUrlHelper() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);
		$translation = $this->createMock(Translation::class);

		$urlGenerator->expects($this->once())
			->method('generate')
			->with(
				$this->equalTo('product.show'),
				$this->equalTo(['id' => 1])
			);

		$block = new Block($path, $template, $urlGenerator, $session, $translation);

		$block->url('product.show', ['id' => 1]);
	}

	public function testCsrfHelper() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);
		$translation = $this->createMock(Translation::class);

		$session->expects($this->once())
			->method('generateCsrfHtml')
			->willReturn('<some csrf input tag>');

		$block = new Block($path, $template, $urlGenerator, $session, $translation);

		$this->assertSame('<some csrf input tag>', $block->csrf());
	}

	public function testCsrfMetaHelper() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);
		$translation = $this->createMock(Translation::class);

		$session->expects($this->once())
			->method('generateCsrfMeta')
			->willReturn('<some csrf meta tags>');

		$block = new Block($path, $template, $urlGenerator, $session, $translation);

		$this->assertSame('<some csrf meta tags>', $block->csrfMeta());
	}

	public function testTranslateHelper() {
		$path = $this->createMock(Path::class);
		$template = $this->createMock(Template::class);
		$urlGenerator = $this->createMock(UrlGenerator::class);
		$session = $this->createMock(Session::class);
		$translation = $this->createMock(Translation::class);

		$translation->expects($this->once())
			->method('translate')
			->willReturn('Some translated text');

		$block = new Block($path, $template, $urlGenerator, $session, $translation);

		$this->assertSame('Some translated text', $block->translate('some.key'));
	}
}
