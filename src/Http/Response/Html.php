<?php
namespace Rise\Http\Response;

use Rise\Http\Response;
use Rise\Template;

class Html {
	/**
	 * Indicate whether the response hooks has been registered or not.
	 * @var bool
	 */
	protected $registered = false;

	/**
	 * @var string
	 */
	protected $contentType = 'text/html';

	/**
	 * @var string
	 */
	protected $charset = 'UTF-8';

	/**
	 * @var string
	 */
	protected $template;

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * @var \Rise\Http\Response
	 */
	protected $response;

	/**
	 * @var \Rise\Template
	 */
	protected $templateService;

	public function __construct(Response $response, Template $templateService) {
		$this->response = $response;
		$this->templateService = $templateService;
	}

	/**
	 * Set template and data.
	 *
	 * @param string $template
	 * @param array $data Optional.
	 * @return self
	 */
	public function render($template, $data = []) {
		$this->template = $template;
		$this->data = $data;
		$this->registerResponseHooks();
		return $this;
	}

	/**
	 * Update data.
	 *
	 * @param array $data
	 * @param bool $recursive Optional. Default to false.
	 * @return self
	 */
	public function update($data, $recursive = false) {
		if (!$recursive) {
			$this->data = array_merge($this->data, $data);
		} else {
			$this->data = array_replace_recursive($this->data, $data);
		}
		return $this;
	}

	/**
	 * @return self
	 */
	public function send() {
		$this->response->send();
		return $this;
	}

	/**
	 * @param string $charset
	 * @return self
	 */
	public function setCharset($charset) {
		$this->charset = $charset;
		return $this;
	}

	protected function registerResponseHooks() {
		if ($this->registered) {
			return;
		}

		$this->response->onBeforeSend(function () {
			$this->beforeSend();
		});

		$this->registered = true;
	}

	protected function beforeSend() {
		$body = $this->templateService->render($this->template, $this->data);
		$response = $this->response;
		$response->setContentType($this->contentType);
		$response->setCharset($this->charset);
		$response->setBody($body);
	}
}
