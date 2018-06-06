<?php
namespace Rise\Test\RouterTest;

use Rise\Http\Request as BaseRequest;

class Request extends BaseRequest {
	public function __construct(string $uri = '', $method = 'GET') {
		$this->requestUri = $uri;
		$this->method = $method;
	}
}
