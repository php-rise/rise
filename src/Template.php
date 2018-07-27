<?php
namespace Rise;

use Rise\Template\Blocks\BlockFactory;

class Template {
	/**
	 * Map of blocks.
	 * Format: [
	 *     '<template path>' => Rise\Template\Blocks\Block
	 * ]
	 * @var array
	 */
	protected $blocks = [];

	/**
	 * @var \Rise\Template\Blocks\BlockFactory
	 */
	protected $blockFactory;

	public function __construct(BlockFactory $blockFactory) {
		$this->blockFactory = $blockFactory;
	}

	/**
	 * Render a block.
	 *
	 * @param string $template
	 * @param array $data
	 * @return string
	 */
	public function render($template = '', $data = []) {
		if (!isset($this->blocks[$template])) {
			$this->blocks[$template] = $this->blockFactory->create($template);
		}
		$block = $this->blocks[$template];
		$block->setData($data);
		return $block->render();
	}
}
