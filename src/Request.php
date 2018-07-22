<?php
namespace Rise;

use Rise\Request\Upload;
use Rise\Router\Result as RouterResult;

class Request {
	/**
	 * HTTP version.
	 *
	 * @var string
	 */
	protected $httpVersion;

	/**
	 * HTTP method.
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * Request path.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Host.
	 *
	 * @var string|null
	 */
	protected $host;

	/**
	 * HTTP PUT variables.
	 *
	 * @var array
	 */
	protected $putParams;

	/**
	 * HTTP DELETE variables.
	 *
	 * @var array
	 */
	protected $deleteParams;

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

		$this->method = $_SERVER['REQUEST_METHOD'];
		$this->path = strtok($_SERVER['REQUEST_URI'], '?');
	}

	/**
	 * Get request HTTP version.
	 *
	 * @return string
	 */
	public function getHttpVersion() {
		if (!isset($this->httpVersion)) {
			$serverProtocol = $_SERVER['SERVER_PROTOCOL'];
			$this->httpVersion = substr($serverProtocol, strpos($serverProtocol, '/') + 1);
		}
		return $this->httpVersion;
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
	public function isMethod($method) {
		return ($this->method === $method);
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
		if (!is_string($key) || $key === '') {
			return $defaultValue;
		}

		$key = strtoupper(str_replace('-', '_', $key));

		return $_SERVER['HTTP_' . $key] ?? $_SERVER[$key] ?? $defaultValue;
	}

	/**
	 * Return HTTP GET variables.
	 *
	 * @return array
	 */
	public function getGetParams() {
		return $_GET ?? [];
	}

	/**
	 * Return HTTP POST variables.
	 *
	 * @return array
	 */
	public function getPostParams() {
		return $_POST ?? [];
	}

	/**
	 * Return HTTP PUT variables.
	 *
	 * @return array
	 */
	public function getPutParams() {
		if (!isset($this->putParams)) {
			if ($this->method === 'PUT') {
				$this->putParams = $this->getParamsFromInput();
			} else {
				$this->putParams = [];
			}
		}

		return $this->putParams;
	}

	/**
	 * Return HTTP DELETE variables.
	 *
	 * @return array
	 */
	public function getDeleteParams() {
		if (!isset($this->deleteParams)) {
			if ($this->method === 'DELETE') {
				$this->deleteParams = $this->getParamsFromInput();
			} else {
				$this->deleteParams = [];
			}
		}

		return $this->deleteParams;
	}

	/**
	 * Get Url parameters.
	 *
	 * @return array
	 */
	public function getUrlParams() {
		return $this->routerResult->getParams();
	}

	/**
	 * Get a specific parameter.
	 *
	 * @param string $key
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function getUrlParam($key, $defaultValue = null) {
		return $this->routerResult->getParam($key, $defaultValue);
	}

	/**
	 * Get uploaded file.
	 *
	 * @return mixed
	 */
	public function getFiles() {
		return $this->upload->getFiles();
	}

	/**
	 * @return array
	 */
	private function getParamsFromInput() {
		$numOfMatches = preg_match('/boundary=(.*)$/', $this->getHeader('Content-Type'), $matches);
		if (!$numOfMatches) {
			parse_str(file_get_contents('php://input'), $params);
		} else {
			$params = [];
		}
		return $params;
	}
}
