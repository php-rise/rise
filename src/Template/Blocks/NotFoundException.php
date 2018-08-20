<?php
namespace Rise\Template\Blocks;

use Exception;

class NotFoundException extends Exception {
	public function __construct($message, $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}
