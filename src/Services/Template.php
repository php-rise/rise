<?php
namespace Rise\Services;

use Rise\Components\Template\Blocks\Block;
use Rise\Components\Template\Blocks\Layout;
use Rise\Components\Template\Blocks\LayoutableBlock;

class Template extends BaseService {
	/**
	 * Render a block.
	 *
	 * @param string $template
	 * @param array $data
	 * @return string
	 */
	public function renderBlock($template = '', $data = []) {
		return (new Block)->setTemplate($template)->setData($data)->getHtml();
	}

	/**
	 * Render a page.
	 *
	 * @param string $template
	 * @param array $data
	 * @return string
	 */
	public function renderPage($template = '', $data = []) {
		$contentBlock = (new LayoutableBlock)->setTemplate($template)->setData($data);
		$contentHtml = $contentBlock->getHtml();

		if ($contentBlock->getLayoutTemplate()) {
			return (new Layout)->setTemplate($contentBlock->getLayoutTemplate())
				->setData($contentBlock->getData())
				->setContentHtml($contentHtml)
				->setOverridenNamedBlocks($contentBlock->getOverridenNamedBlocks())
				->getHtml();
		}

		return $contentHtml;
	}
}
