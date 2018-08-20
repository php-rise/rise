<?php
namespace Rise\Template\Blocks;

use Exception;
use Rise\Path;
use Rise\Template;
use Rise\Router\UrlGenerator;
use Rise\Session;
use Rise\Translation;

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

	/**
	 * @var \Rise\Translation
	 */
	protected $translation;

	public function __construct(
		Path $pathService,
		Template $templateService,
		UrlGenerator $urlGenerator,
		Session $session,
		Translation $translation
	) {
		$this->pathService = $pathService;
		$this->templateService = $templateService;
		$this->urlGenerator = $urlGenerator;
		$this->session = $session;
		$this->translation = $translation;
	}

	/**
	 * Helper function for rendering block in block template.
	 *
	 * @param string $template
	 * @param array $data
	 * @return string
	 */
	public function include($template, $data = []) {
		return $this->templateService->render($this->resolveTemplatePath($template), $data);
	}

	/**
	 * Set extended template and data.
	 *
	 * @param string $template
	 * @param array $data Optional.
	 * @param string $paramName Optional. Variable name of the variable storing the content of this block, default to "body".
	 */
	public function extend($template, $data = [], $paramName = 'body') {
		$this->extendedTemplate = $this->resolveTemplatePath($template);
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
	 * Helper function for translation.
	 *
	 * @param string $key
	 * @param string $defaultValue
	 * @param string $locale
	 * @return string
	 */
	public function translate($key, $defaultValue = '', $locale = null) {
		return $this->translation->translate($key, $defaultValue, $locale);
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
	public function setTemplate($template) {
		$this->template = $this->resolveTemplatePath($template);
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
	 * Resolve template path.
	 *
	 * @param string $path
	 * @return string
	 */
	protected function resolveTemplatePath($path) {
		switch ($path[0]) {
		case '/':
			break;
		case '.':
			$path = realpath(dirname($this->template) . '/' . $path . '.phtml');
			break;
		default:
			$path = realpath($this->pathService->getTemplatesPath() . '/' . $path . '.phtml');
			break;
		}

		if ($path === false) {
			throw new NotFoundException($this->template . ' file not found');
		}

		return $path;
	}

	/**
	 * Render this block to HTML string.
	 *
	 * @return string
	 */
	protected function renderToHtml() {
		extract($this->data, EXTR_SKIP);
		ob_start();
		set_error_handler([$this, 'handleError']);
		include $this->template;
		restore_error_handler();
		$html = ob_get_clean();
		return $html;
	}

	/**
	 * Error handler.
	 */
	protected function handleError($errno, $errstr, $errfile, $errline) {
		ob_end_clean();
		throw new BlockException($errstr, 0, $errno, $errfile, $errline);
	}
}
