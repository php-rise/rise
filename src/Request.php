<?php
namespace Rise;

use Rise\Request\Upload;
use Rise\Router\Result as RouterResult;

class Request {
	/**
	 * Request path.
	 */
	protected $path = '';

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
	protected $upload;

	/**
	 * @var \Rise\Router\Result
	 */
	protected $routerResult;

	public function __construct(Upload $upload, RouterResult $routerResult) {
		$this->upload = $upload;
		$this->routerResult = $routerResult;
		$this->path = strtok($_SERVER['REQUEST_URI'], '?');
		$this->method = $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Get request path.
	 *
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Set request path.
	 *
	 * @param string $path
	 * @return self
	 */
	public function setPath($path) {
		$this->path = $path;
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
	 * Get header value.
	 *
	 * @param string $key
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function getHeader($key, $defaultValue = null) {
		if (empty($key)) {
			return $defaultValue;
		}

		$key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));

		if (isset($_SERVER[$key])) {
			return $_SERVER[$key];
		}

		return $defaultValue;
	}

	/**
	 * Get Url parameters.
	 *
	 * @return array
	 */
	public function getParams() {
		return $this->routerResult->getParams();
	}

	/**
	 * Get a specific parameter.
	 *
	 * @param string $key
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function getParam($key, $defaultValue = null) {
		return $this->routerResult->getParam($key, $defaultValue);
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
		return $this->upload->getFile($key);
	}
}
