<?php
namespace Rise\Template\Blocks;

use Exception;
use Rise\Path;
use Rise\Template;
use Rise\Router\UrlGenerator;
use Rise\Session;

class Block {
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
	 * Template location of extended template.
	 *
	 * @var string
	 */
	protected $extendedTemplate = '';

	/**
	 * @var array
	 */
	protected $extendedData = [];

	/**
	 * Variable name of the variable storing the content of this block, default to "body".
	 *
	 * @var string
	 */
	protected $extendedParamName = 'body';

	/**
	 * @var \Rise\Path
	 */
	protected $pathService;

	/**
	 * @var \Rise\Template
	 */
	protected $templateService;

	/**
	 * @var \Rise\Router\UrlGenerator
	 */
	protected $urlGenerator;

	/**
	 * @var \Rise\Session
	 */
	protected $session;

	public function __construct(
		Path $pathService,
		Template $templateService,
		UrlGenerator $urlGenerator,
		Session $session
	) {
		$this->pathService = $pathService;
		$this->templateService = $templateService;
		$this->urlGenerator = $urlGenerator;
		$this->session = $session;
	}

	/**
	 * Helper function for rendering block in block template.
	 *
	 * @param string $template
	 * @param array $data
	 * @return string
	 */
	public function include($template, $data = []) {
		return $this->templateService->render($template, $data);
	}

	/**
	 * Set extended template and data.
	 *
	 * @param string $template
	 * @param array $data Optional.
	 * @param string $paramName Optional. Variable name of the variable storing the content of this block, default to "body".
	 */
	public function extend($template, $data = [], $paramName = 'body') {
		$this->extendedTemplate = $template;
		if (is_array($data)) {
			$this->extendedData = $data;
		}
		if (is_string($paramName) && $paramName) {
			$this->extendedParamName = $paramName;
		}
	}

	/**
	 * Helper function for generating url.
	 *
	 * @param string $name
	 * @param array $params
	 * @return string
	 */
	public function url($name, $params = []) {
		return $this->urlGenerator->generate($name, $params);
	}

	/**
	 * Helper function for generating CSRF HTML.
	 *
	 * @return string
	 */
	public function csrf() {
		return $this->session->generateCsrfHtml();
	}

	/**
	 * Helper function for generating CSRF meta HTML.
	 *
	 * @return string
	 */
	public function csrfMeta() {
		return $this->session->generateCsrfMeta();
	}

	/**
	 * Helper function for getting CSRF form key.
	 *
	 * @return string
	 */
	public function csrfKey() {
		return $this->session->getCsrfTokenFormKey();
	}

	/**
	 * Helper function for getting CSRF token.
	 *
	 * @return string
	 */
	public function csrfValue() {
		return $this->session->getCsrfToken();
	}

	/**
	 * Render a template.
	 *
	 * @return self
	 */
	public function render() {
		$html = $this->renderToHtml();

		if (!empty($this->extendedTemplate)) {
			$data = [$this->extendedParamName => $html] + $this->extendedData + $this->data;
			$html = $this->templateService->render($this->extendedTemplate, $data);
		}

		return $html;
	}

	/**
	 * Set template location.
	 *
	 * @param string $template
	 */
	public function setTemplate($template = '') {
		$this->template = $template;
	}

	/**
	 * Set variables used in template.
	 *
	 * @param array $data
	 */
	public function setData($data = []) {
		if (is_array($data)) {
			$this->data = $data + $this->data;
		}
	}

	/**
	 * Render this block to HTML string.
	 *
	 * @return string
	 */
	protected function renderToHtml() {
		try {
			extract($this->data, EXTR_SKIP);
			ob_start();
			include $this->pathService->getTemplatesPath() . '/' . $this->template . '.phtml';
			$html = ob_get_clean();
		} catch (Exception $e) {
			$html = '';
		}
		return $html;
	}
}
