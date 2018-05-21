<?php
namespace Rise\Services\Http;

use Rise\Services\BaseService;
use Rise\Factories\Http\RequestFactory;

class Receiver extends BaseService {
	/**
	 * @var \Rise\Components\Http\Request|null
	 */
	protected $request = null;

	/**
	 * @var \Rise\Factories\Http\RequestFactory
	 */
	protected $requestFactory;

	public function __construct(RequestFactory $requestFactory) {
		$this->requestFactory = $requestFactory;
	}

	/**
	 * @return \Rise\Components\Http\Request
	 */
	public function getRequest() {
		return $this->request ? $this->request : $this->createRequest();
	}

	/**
	 * @return \Rise\Components\Http\Request
	 */
	private function createRequest() {
		$this->request = $this->requestFactory->create('1111')->setMethod($_SERVER['REQUEST_METHOD'])
			->setRequestUri($_SERVER['REQUEST_URI']);
		return $this->request;
	}
}
