<?php
namespace Rise\Services;

use Rise\Factories\Template\Blocks\BlockFactory;
use Rise\Factories\Template\Blocks\LayoutFactory;
use Rise\Factories\Template\Blocks\LayoutableBlockFactory;

class Template extends BaseService {
	/**
	 * @var Rise\Factories\Template\Blocks\BlockFactory
	 */
	private $blockFactory;

	/**
	 * @var Rise\Factories\Template\Blocks\LayoutFactory
	 */
	private $layoutFactory;

	/**
	 * @var Rise\Factories\Template\Blocks\LayoutableBlockFactory
	 */
	private $LayoutableBlockFactory;

	public function __construct(
		BlockFactory $blockFactory,
		LayoutFactory $layoutFactory,
		LayoutableBlockFactory $layoutableBlockFactory
	) {
		$this->blockFactory = $blockFactory;
		$this->layoutFactory = $layoutFactory;
		$this->layoutableBlockFactory = $layoutableBlockFactory;
	}

	/**
	 * Render a block.
	 *
	 * @param string $template
	 * @param array $data
	 * @return string
	 */
	public function renderBlock($template = '', $data = []) {
		return $this->blockFactory->create()
			->setTemplate($template)
			->setData($data)->getHtml();
	}

	/**
	 * Render a page.
	 *
	 * @param string $template
	 * @param array $data
	 * @return string
	 */
	public function renderPage($template = '', $data = []) {
		$contentBlock = $this->layoutableBlockFactory->create()
			->setTemplate($template)
			->setData($data);
		$contentHtml = $contentBlock->getHtml();

		if ($contentBlock->getLayoutTemplate()) {
			return $this->layoutFactory->create()
				->setTemplate($contentBlock->getLayoutTemplate())
				->setData($contentBlock->getData())
				->setContentHtml($contentHtml)
				->setOverridenNamedBlocks($contentBlock->getOverridenNamedBlocks())
				->getHtml();
		}

		return $contentHtml;
	}
}
