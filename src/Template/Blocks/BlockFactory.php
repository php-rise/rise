<?php
namespace Rise\Template\Blocks;

use Rise\Container\BaseFactory;

class BlockFactory extends BaseFactory {
	public function create() {
		return $this->container->getNewInstance('Rise\Template\Blocks\Block');
	}
}
