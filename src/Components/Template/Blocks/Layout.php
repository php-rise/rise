<?php
namespace Rise\Components\Template\Blocks;

class Layout extends Block {
	/**
	 * {@inheritdoc}
	 */
	protected $templateDirectory = 'layouts';

	/**
	 * @var string
	 */
	protected $contentHtml = '';

	/**
	 * Format: [
	 *     '<name>': ['<template>', <template variables map>],
	 *     ...
	 * ]
	 *
	 * @var array
	 */
	protected $overridenNamedBlocks = [];

	public function getContentHtml() {
		return $this->contentHtml;
	}

	public function setContentHtml($contentHtml) {
		$this->contentHtml = $contentHtml;
		return $this;
	}

	/**
	 * @param array $overridenNamedBlocks
	 * @return self
	 */
	public function setOverridenNamedBlocks($overridenNamedBlocks) {
		$this->overridenNamedBlocks = $overridenNamedBlocks;
		return $this;
	}

	/**
	 * Helper function used in layout template.
	 *
	 * @return string
	 */
	public function content() {
		return $this->getContentHtml();
	}

	/**
	 * Helper function for rendering block in layout template.
	 *
	 * @param string|null $name
	 * @param string $template
	 * @param array $data
	 * @return string
	 */
	public function block($name = null, $template = '', $data = []) {
		if ($name && isset($this->overridenNamedBlocks[$name])) {
			$namedBlock = $this->overridenNamedBlocks[$name];
			return $this->templateService->renderBlock($namedBlock['template'], $namedBlock['data']);
		}
		return $this->templateService->renderBlock($template, $data);
	}
}
