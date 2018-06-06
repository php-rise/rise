<?php
namespace Rise\Test\DispatcherTest;

use Rise\Http\Response as BaseResponse;

class Response extends BaseResponse {
	public function __construct() {
	}

	public function send() {
	}

	public function getStatusCode() {
		return $this->statusCode;
	}
}
