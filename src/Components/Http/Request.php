<?php
namespace Rise\Components\Http;

class Request {
	/**
	 * Request URI. Same as $_SERVER['REQUEST_URI']
	 *
	 * @var string
	 */
	protected $requestUri = '';

	/**
	 * Request path. Request URL without the locale code.
	 */
	protected $requestPath = '';

	/**
	 * HTTP method.
	 *
	 * @var string
	 */
	protected $method = '';

	/**
	 * Url parameters. Key value pairs.
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 * Get request URI.
	 *
	 * @return string
	 */
	public function getRequestUri() {
		return $this->requestUri;
	}

	/**
	 * Set request URI.
	 *
	 * @param string $requestUri
	 * @return self
	 */
	public function setRequestUri($requestUri = '') {
		$this->requestUri = $requestUri;
		return $this;
	}

	/**
	 * Get request path.
	 *
	 * @return string
	 */
	public function getRequestPath() {
		return $this->requestPath;
	}

	/**
	 * Set request path.
	 *
	 * @param string $requestPath
	 * @return self
	 */
	public function setRequestPath($requestPath = '') {
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
	 * Set HTTP method.
	 *
	 * @param string $method
	 * @return self
	 */
	public function setMethod($method = '') {
		$this->method = $method;
		return $this;
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
			$result = $defaultValue;
		}
		return $result;
	}

	/**
	 * Get upload file tmp name.
	 *
	 * @param string $key
	 * @return \Rise\Components\Http\Request\File
	 */
	public function getFile($key) {
		return (new File)->setKey($key);
	}
}
