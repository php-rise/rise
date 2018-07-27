<?php
namespace Rise\Template\Blocks;

use Exception;

class BlockException extends Exception {
	public function __construct($message, $code = 0, $errno, $errfile, $errline, Exception $previous = null) {
		$message = "In $errfile on line $errline : $message";
		parent::__construct($message, $code, $previous);
		$this->errno = $errno;
		$this->errfile = $errfile;
		$this->errline = $errline;
	}
}
