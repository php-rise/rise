<?php
/**
 * Copyright (c) Jack Wan <hwguyguy@gmail.com> - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Jack Wan <hwguyguy@gmail.com>, August 2015
 */
namespace Rise\Components\Template\Blocks;

/**
 * Simple template engine.
 *
 * @author Jack Wan <hwguyguy@gmail.com>
 */
class Block {
	/**
	 * Template directory.
	 *
	 * @var string
	 */
	protected $templateDirectory = '';

	/**
	 * Template location.
	 *
	 * @var string
	 */
	protected $template = '';

	/**
	 * Key value pairs.
	 * Format: [
	 *     '<variable name in template>' => <variable value>,
	 *     ...
	 * ]
	 * Example: [
	 *     'id' => 1,
	 *     'name' => 'Jack One',
	 *     'genius' => true,
	 * ]
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * Generated html content.
	 *
	 * @var string|null
	 */
	protected $html = null;

	/**
	 * Set template directory.
	 *
	 * @param string $path
	 * @return self
	 */
	public function setTemplateDirectory($path = '') {
		$this->templateDirectory = rtrim($path, '/');
		return $this;
	}

	/**
	 * Set template location.
	 *
	 * @param string $template
	 * @return self
	 */
	public function setTemplate($template = '') {
		$this->template = $template;
		return $this;
	}

	/**
	 * Get variables used in template.
	 *
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Set variables used in template.
	 *
	 * @param array $data
	 * @return self
	 */
	public function setData($data = []) {
		$this->data = $data + $this->data;
		return $this;
	}

	/**
	 * Render a template.
	 *
	 * @return self
	 */
	public function render() {
		try {
			$data = [];
			$otherData = [];
			foreach ($this->getData() as $key => $value) {
				if ($value instanceof Block) {
					$data[$key] = $value->getHtml();
					$otherData = $otherData + $value->getData(); // get child template data
				} else {
					$data[$key] = $value;
				}
			}
			$data = $data + $otherData;
			extract($data, EXTR_SKIP);
			ob_start();
			include service('path')->getTemplatesPath() . '/' . $this->templateDirectory . '/' . $this->template . '.phtml';
			$html = ob_get_clean();
		} catch (Exception $e) {
			$html = '';
		}
		$this->html = $html;
		return $this;
	}

	/**
	 * Get html of a template.
	 *
	 * @param bool $rerender optional
	 * @return string
	 */
	public function getHtml($rerender = false) {
		if ($this->html === null || $rerender) {
			$this->render();
		}
		return $this->html;
	}

	/**
	 * Helper function for rendering block in block template.
	 *
	 * @param string $template
	 * @param array $data
	 * @return string
	 */
	public function include($template = '', $data = []) {
		return service('template')->renderBlock($template, $data);
	}
}
