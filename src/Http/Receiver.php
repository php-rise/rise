<?php
namespace Rise\Http;

use Rise\Http\Receiver\RequestFactory;

class Receiver {
	/**
	 * @var \Rise\Http\Receiver\Request|null
	 */
	protected $request = null;

	/**
	 * @var \Rise\Http\Receiver\RequestFactory
	 */
	protected $requestFactory;

	public function __construct(RequestFactory $requestFactory) {
		$this->requestFactory = $requestFactory;
	}

	/**
	 * @return \Rise\Http\Receiver\Request
	 */
	public function getRequest() {
		return $this->request ? $this->request : $this->createRequest();
	}

	/**
	 * @return \Rise\Http\Receiver\Request
	 */
	private function createRequest() {
		$this->request = $this->requestFactory->create()->setMethod($_SERVER['REQUEST_METHOD'])
			->setRequestUri($_SERVER['REQUEST_URI']);
		return $this->request;
	}
}
