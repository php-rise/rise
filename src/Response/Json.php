<?php
namespace Rise\Response;

use Rise\Response;

class Json {
	/**
	 * Indicate whether the response hooks has been registered or not.
	 * @var bool
	 */
	protected $registered = false;

	/**
	 * @var string
	 */
	protected $contentType = 'application/json';

	/**
	 * @var string
	 */
	protected $charset = 'UTF-8';

	/**
	 * @var mixed
	 */
	protected $data = null;

	/**
	 * @var \Rise\Response
	 */
	protected $response;

	public function __construct(Response $response) {
		$this->response = $response;
	}

	/**
	 * Set data.
	 *
	 * @param mixed $data
	 * @return self
	 */
	public function data($data) {
		$this->data = $data;
		$this->registerResponseHooks();
		return $this;
	}

	/**
	 * Update array data.
	 *
	 * @param array $data
	 * @param bool $recursive Optional. Default to false.
	 * @return self
	 */
	public function update($data, $recursive = false) {
		if (!is_array($data)) {
			return $this->data($data);
		}

		if (is_array($this->data)) {
			if (!$recursive) {
				$this->data = array_merge($this->data, $data);
			} else {
				$this->data = array_replace_recursive($this->data, $data);
			}
		} else {
			$this->data = $data;
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
		$response = $this->response;
		$response->setContentType($this->contentType);
		$response->setCharset($this->charset);
		$response->setBody(json_encode($this->data));
	}
}
