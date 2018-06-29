<?php
namespace Rise\Template\Blocks;

use Rise\Container\BaseFactory;

class BlockFactory extends BaseFactory {
	/**
	 * @param string $template
	 * @param array $data
	 */
	public function create($template, $data = []) {
		$block = $this->container->getNewInstance(Block::class);
		$block->setTemplate($template);
		$block->setData($data);
		return $block;
	}
}
