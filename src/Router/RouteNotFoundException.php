<?php
namespace Rise\Router;

use Exception;

class RouteNotFoundException extends Exception {
	public function __construct($message = 'Route not found', $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}
