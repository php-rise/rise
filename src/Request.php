<?php
namespace Rise;

use Rise\Request\Upload;

class Request {
	/**
	 * Request URI. Same as $_SERVER['REQUEST_URI']
	 *
	 * @var string
	 */
	protected $requestUri = '';

	/**
	 * Request path, default is same as request URI, but can be changed in runtime.
	 */
	protected $requestPath = '';

	/**
	 * HTTP method.
	 *
	 * @var string
	 */
	protected $method = '';

	/**
	 * Host.
	 *
	 * @var string|null
	 */
	protected $host = null;

	/**
	 * Url parameters. Key value pairs.
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 * @var \Rise\Upload
	 */
	protected $httpUpload;

	public function __construct(Upload $upload) {
		$this->requestUri = $_SERVER['REQUEST_URI'];
		$this->method = $_SERVER['REQUEST_METHOD'];
		$this->httpUpload = $upload;
	}

	/**
	 * Get request URI.
	 *
	 * @return string
	 */
	public function getRequestUri() {
		return $this->requestUri;
	}

	/**
	 * Get request path.
	 *
	 * @return string
	 */
	public function getRequestPath() {
		return $this->requestPath ? $this->requestPath : $this->getRequestUri();
	}

	/**
	 * Set request path.
	 *
	 * @param string $requestPath
	 * @return self
	 */
	public function setRequestPath($requestPath) {
		$this->requestPath = $requestPath;
		return $this;
	}

	/**
	 * Get HTTP method of the request.
	 *
	 * @return string
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * Check HTTP method of the request.
	 *
	 * @return bool
	 */
	public function isMethod($method = '') {
		return ($this->method === $method);
	}

	/**
	 * Get HTTP host.
	 *
	 * @return string
	 */
	public function getHost() {
		if (!is_null($this->host)) {
			return $this->host;
		}

		if ($_SERVER['HTTP_X_FORWARDED_HOST']) {
			$elements = explode(',', $value);
			$host = trim(end($elements));
		} else if ($_SERVER['HTTP_HOST']) {
			$host = $_SERVER['HTTP_HOST'];
		} else if ($_SERVER['SERVER_NAME']) {
			$host = $_SERVER['SERVER_NAME'];
		}

		if (isset($host)) {
			$host = preg_replace('/:\d+$/', '', $host);
			$this->host = $host;
		}

		return $this->host;
	}

	/**
	 * Get Url parameters.
	 *
	 * @return array
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * Set Url parameters.
	 *
	 * @param array $params
	 * @return self
	 */
	public function setParams($params = []) {
		$this->params = $params;
		return $this;
	}

	/**
	 * Get a specific parameter.
	 *
	 * @param string $key
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function getParam($key, $defaultValue = null) {
		if (array_key_exists($key, $this->params)) {
			return $this->params[$key];
		}
		return $defaultValue;
	}

	/**
	 * Get a query parameter.
	 *
	 * @param string $key
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function getQuery($key, $defaultValue = null) {
		if (array_key_exists($key, $_GET)) {
			return $_GET[$key];
		}
		return $defaultValue;
	}

	/**
	 * Get a POST parameter.
	 *
	 * @param string $key
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function getInput($key, $defaultValue = null) {
		if (array_key_exists($key, $_POST)) {
			return $_POST[$key];
		}
		return $defaultValue;
	}

	/**
	 * Get uploaded file.
	 *
	 * @param string $key
	 * @return \Rise\Request\Upload\File|\Rise\Request\Upload\File[]|null
	 */
	public function getFile($key) {
		return $this->httpUpload->getFile($key);
	}

	/**
	 * @param string $key
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function get($key, $defaultValue = null) {
		$result = $this->getInput($key);
		if ($result === null) {
			$result = $this->getParam($key);
		}
		if ($result === null) {
			$result = $this->getQuery($key);
		}
		if ($result === null) {
			$result = $this->getFile($key);
		}
		if ($result === null) {
			$result = $defaultValue;
		}
		return $result;
	}
}
