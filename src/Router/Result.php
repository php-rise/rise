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
	 * @return bool
	 */
	public function hasHandler() {
		return !is_null($this->handler);
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
	public function setHandler($handler, $status = 200) {
		$this->status = $status;
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
}
