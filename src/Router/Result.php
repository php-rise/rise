<?php
namespace Rise\Router;

class Result {
	/**
	 * HTTP status code.
	 * @var int
	 */
	protected $status = 404;

	/**
	 * @var mixed
	 */
	protected $handler = null;

	/**
	 * @var array
	 */
	protected $params = [];

	/**
	 * @return mixed
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @param int $status
	 */
	public function setStatus($status) {
		$this->status = $status;
	}

	/**
	 * @return bool
	 */
	public function hasHandler() {
		return !empty($this->handler);
	}

	/**
	 * @return mixed
	 */
	public function getHandler() {
		return $this->handler;
	}

	/**
	 * @param mixed $handler
	 */
	public function setHandler($handler) {
		$this->handler = $handler;
	}

	/**
	 * @return array
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * @param array $params
	 */
	public function setParams($params) {
		$this->params = $params;
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
}
