<?php
namespace Rise\Test\DispatcherTest;

use Rise\Session as BaseSession;

class Session extends BaseSession {
	private $toggled = false;

	public function __construct() {
	}

	public function getToggled() {
		return $this->toggled;
	}

	public function toggleCurrentFlashBagKey() {
		$this->toggled = true;
		return $this;
	}

	public function clearFlash() {
		return $this;
	}

	public function rememberCsrfToken() {
		return $this;
	}
}
