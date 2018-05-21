<?php
namespace Rise\Factories\Template\Blocks;

use Rise\Factories\BaseFactory;

class BlockFactory extends BaseFactory {
	public function create() {
		return $this->container->get('Rise\Components\Template\Blocks\Block');
	}
}
