<?php
namespace Rise\Template\Blocks;

class LayoutableBlock extends Block {
	/**
	 * @var string
	 */
	protected $layoutTemplate = '';

	/**
	 * Format: [
	 *     '<name>': [
	 *         'template' => '<template>',
	 *         'data' => <template variables map>
	 *     ],
	 *     ...
	 * ]
	 *
	 * @var array
	 */
	protected $overridenNamedBlocks = [];

	/**
	 * @return string
	 */
	public function getLayoutTemplate() {
		return $this->layoutTemplate;
	}

	/**
	 * @param string $layoutTemplate
	 * @return self
	 */
	public function setLayoutTemplate($layoutTemplate) {
		$this->layoutTemplate = $layoutTemplate;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getOverridenNamedBlocks() {
		return $this->overridenNamedBlocks;
	}

	/**
	 * @param string $name
	 * @param string $template
	 * @param array $data
	 * @return self
	 */
	public function addOverridenNamedBlock($name, $template, $data) {
		$this->overridenNamedBlocks[$name] = [
			'template' => $template,
			'data' => $data
		];
		return $this;
	}

	/**
	 * Helper function for assigning layout in block template.
	 *
	 * @param string $template
	 * @return self
	 */
	public function layout($template = '') {
		$this->setLayoutTemplate($template);
		return $this;
	}

	/**
	 * Helper function for assigning block in block template.
	 *
	 * @param string $name
	 * @param string $template
	 * @param array $data
	 * @return self
	 */
	public function block($name, $template = '', $data = []) {
		$this->addOverridenNamedBlock($name, $template, $data);
		return $this;
	}
}
