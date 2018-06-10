<?php
namespace Rise;

class Session {
	const SAVE_HANDLER_FILE = 1;
	const SAVE_HANDLER_REDIS = 2;

	/**
	 * @var string
	 */
	protected $sessionName = 'rise_session';

	/**
	 * @var bool
	 */
	protected $started = false;

	/**
	 * @var int
	 */
	protected $saveHandler = 1;

	/**
	 * @var string
	 */
	protected $csrfToken = '';

	/**
	 * @var string
	 */
	protected $csrfTokenSessionKey = 'csrfToken';

	/**
	 * @var string
	 */
	protected $csrfTokenFormKey = '_csrf';

	/**
	 * @var \Rise\Path
	 */
	protected $path;

	public function __construct(Path $path) {
		$this->path = $path;

		$this->readConfigurations();
	}

	/**
	 * Read configuration file.
	 *
	 * @return self
	 */
	public function readConfigurations() {
		$file = $this->path->getConfigurationsPath() . '/session.php';
		if (file_exists($file)) {
			$configurations = require($file);
			if (isset($configurations['sessionName'])) {
				$this->sessionName = $configurations['sessionName'];
			}
		}
		return $this;
	}

	/**
	 * @return self
	 */
	public function start() {
		if ($this->started) {
			return;
		}
		switch ($this->saveHandler) {
		case static::SAVE_HANDLER_FILE:
			session_save_path($this->path->getSessionsPath());
			break;
		case static::SAVE_HANDLER_REDIS:
			ini_set('session.save_handler', 'redis');
			ini_set('session.save_path', 'tcp://127.0.0.1:6379');
			break;
		}
		session_name($this->sessionName);
		session_start();
		$this->started = true;
		return $this;
	}

	/**
	 * @return self
	 */
	public function destroy() {
		session_destroy();
		$this->started = false;
		return $this;
	}

	/**
	 * @return self
	 */
	public function regenerate() {
		if (!$this->started) {
			$this->start();
		}
		session_regenerate_id(true);
		return $this;
	}

	/**
	 * Get a session value by key.
	 *
	 * @param string|array $key
	 * @return mixed
	 */
	public function get($key = null) {
		if (!$this->started) {
			$this->start();
		}
		if (is_string($key)) {
			if (isset($_SESSION[$key])) {
				return $_SESSION[$key];
			} else {
				return null;
			}
		} elseif (is_array($key)) {
			$session = &$_SESSION;
			foreach ($key as $_key) {
				if (isset($session[$_key])) {
					$session = &$session[$_key];
				} else {
					return null;
				}
			}
			return $session;
		}
		return $_SESSION;
	}

	/**
	 * Assign a value to a session key.
	 *
	 * @param string|array $key When $key is an array, the value will be stored in a multi-dimensional array.
	 * @param mixed $value
	 * @return self
	 */
	public function set($key, $value) {
		if (!$this->started) {
			$this->start();
		}
		if (is_string($key)) {
			$_SESSION[$key] = $value;
		} elseif (is_array($key)) {
			$session = &$_SESSION;
			foreach ($key as $_key) {
				$session = &$session[$_key];
			}
			$session = $value;
		}
		return $this;
	}

	/**
	 * Push a value to the array assigned by a session key.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return self
	 */
	public function add($key, $value) {
		if (!$this->started) {
			$this->start();
		}
		if (is_string($key)) {
			if (!is_array($_SESSION[$key])) {
				$_SESSION[$key] = [];
			}
			$_SESSION[$key][] = $value;
		} elseif (is_array($key)) {
			$session = &$_SESSION;
			foreach ($key as $_key) {
				$session = &$session[$_key];
			}
			if (!is_array($session)) {
				$session = [];
			}
			$session[] = $value;
		}
		return $this;
	}

	/**
	 * Unset a session key.
	 *
	 * When the key is more than 5 levels, the value will be set to null instead of unsetting it.
	 *
	 * @param string|array $key
	 * @return self
	 */
	public function unset($key) {
		if (!$this->started) {
			$this->start();
		}
		if (is_string($key)) {
			if (isset($_SESSION[$key])) {
				unset($_SESSION[$key]);
			}
		} elseif (is_array($key)) {
			switch (count($key)) {
			case 0:
				break;
			case 1:
				unset($_SESSION[$key[0]]);
				break;
			case 2:
				unset($_SESSION[$key[0]][$key[1]]);
				break;
			case 3:
				unset($_SESSION[$key[0]][$key[1]][$key[2]]);
				break;
			case 4:
				unset($_SESSION[$key[0]][$key[1]][$key[2]][$key[3]]);
				break;
			case 5:
				unset($_SESSION[$key[0]][$key[1]][$key[2]][$key[3]][$key[4]]);
				break;
			default:
				$session = &$_SESSION;
				foreach ($key as $_key) {
					if (isset($session[$_key])) {
						$session = &$session[$_key];
					} else {
						$session = null;
						break;
					}
				}
				if ($session) {
					$session = null;
				}
				break;
			}
		}
		return $this;
	}

	/**
	 * Get data from the current flash messages bag.
	 *
	 * @param string|array $key
	 * @return mixed
	 */
	public function getFlash($key) {
		return $this->get(array_merge(['flashMessageBags', $this->getCurrentFlashBagKey()], (array)$key));
	}

	/**
	 * Set data to the other flash messages bag for the next request.
	 *
	 * @param string|array $key
	 * @param mixed $value
	 * @return self
	 */
	public function setFlash($key, $value) {
		return $this->set(array_merge(['flashMessageBags', $this->getOtherFlashBagKey()], (array)$key), $value);
	}

	/**
	 * Add data to the other flash messages bag for the next request.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return self
	 */
	public function addFlash($key, $value) {
		return $this->add(array_merge(['flashMessageBags', $this->getOtherFlashBagKey()], (array)$key), $value);
	}

	/**
	 * Get the key indicating the current flash messages bag.
	 *
	 * The keys are either "flip" or "flop";
	 *
	 * @return string
	 */
	public function getCurrentFlashBagKey() {
		$key = $this->get('flashMessageBagCurrentKey');
		if (!$key) {
			$key = 'flip';
			$this->set('flashMessageBagCurrentKey', $key);
		}
		return $key;
	}

	/**
	 * Get the key indicating the other flash messages bag.
	 *
	 * The keys are either "flip" or "flop";
	 *
	 * @return string
	 */
	public function getOtherFlashBagKey() {
		return $this->getCurrentFlashBagKey() === 'flip' ? 'flop' : 'flip';
	}

	/**
	 * Change the key indicating the current flash messages bag to another one.
	 *
	 * The keys are either "flip" or "flop";
	 *
	 * @return self
	 */
	public function toggleCurrentFlashBagKey() {
		$this->set(
			'flashMessageBagCurrentKey',
			$this->get('flashMessageBagCurrentKey') === 'flip' ? 'flop' : 'flip'
		);
		return $this;
	}

	/**
	 * Clear current flash message bag.
	 *
	 * @return self
	 */
	public function clearFlash() {
		return $this->unset(['flashMessageBags', $this->getCurrentFlashBagKey()]);
	}

	/**
	 * Keep flash message after the next request.
	 *
	 * @param string $key
	 * @return self
	 */
	public function keepFlash($key) {
		return $this->setFlash($key, $this->getFlash($key));
	}

	/**
	 * Generate CSRF token.
	 *
	 * @return string
	 */
	public function generateCsrfToken() {
		return hash("sha512", mt_rand(0, mt_getrandmax()));
	}

	/**
	 * Get CSRF token.
	 *
	 * @return string
	 */
	public function getCsrfToken() {
		if (!$this->csrfToken) {
			$this->csrfToken = $this->generateCsrfToken();
		}
		return $this->csrfToken;
	}

	/**
	 * Get CSRF token form key.
	 *
	 * @return string
	 */
	public function getCsrfTokenFormKey() {
		return $this->csrfTokenFormKey;
	}

	/**
	 * Store the token in the session.
	 *
	 * @return self
	 */
	public function rememberCsrfToken() {
		if ($this->csrfToken) {
			$this->set($this->csrfTokenSessionKey, $this->csrfToken);
		} else {
			$this->unset($this->csrfTokenSessionKey);
		}
		return $this;
	}

	/**
	 * Validate with the token stored in session.
	 *
	 * @param string $token
	 * @return bool
	 */
	public function validateCsrfToken($token = '') {
		$csrfToken = $this->get($this->csrfTokenSessionKey);
		return ($csrfToken && $csrfToken === $token);
	}

	/**
	 * Generate HTML.
	 *
	 * @return string
	 */
	public function generateCsrfHtml() {
		return '<input type="hidden" name="' . $this->csrfTokenFormKey . '" value="' . $this->getCsrfToken() . '">';
	}
}
