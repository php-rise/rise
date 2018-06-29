<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use Rise\Template;
use Rise\Template\Blocks\Block;
use Rise\Template\Blocks\BlockFactory;

final class TemplateTest extends TestCase {
	public function testRender() {
		$factory = $this->createMock(BlockFactory::class);
		$block = $this->createMock(Block::class);

		$factory->expects($this->once())
			->method('create')
			->willReturn($block);

		$block->expects($this->once())
			->method('render');

		$template = new Template($factory);

		$template->render('template/path', ['content' => 'This is content']);
	}

	public function testRenderSameBlockAgain() {
		$factory = $this->createMock(BlockFactory::class);
		$block = $this->createMock(Block::class);

		$factory->expects($this->once())
			->method('create')
			->willReturn($block);

		$block->expects($this->exactly(2))
			->method('setData')
			->withConsecutive(
				[$this->equalTo(['id' => 1, 'something' => 'Something'])],
				[$this->equalTo(['id' => 2, 'anything' => 'Anything'])]
			);

		$block->expects($this->exactly(2))
			->method('render');

		$template = new Template($factory);

		$template->render('template/path', ['id' => 1, 'something' => 'Something']);
		$template->render('template/path', ['id' => 2, 'anything' => 'Anything']);
	}
}
