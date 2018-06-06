<?php
namespace Rise\Test\LocaleTest;

use Rise\Http\Request as BaseRequest;

class Request extends BaseRequest {
	public function __construct(string $uri = '') {
		$this->requestUri = $uri;
	}
}
