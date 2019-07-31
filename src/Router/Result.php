<?php
namespace Rise\Router;

class Result {
	/**
	 * @var mixed
	 */
	protected $handler = null;

	/**
	 * @var array
	 */
	protected $params = [];

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
		return $this->params[$key] ?? $defaultValue;
	}
}
