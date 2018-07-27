<?php
namespace Rise;

class Session {
	/**
	 * @var bool
	 */
	protected $enabled = false;

	/**
	 * @var bool
	 */
	protected $started = false;

	/**
	 * Default options for session_start()
	 * @var array
	 */
	protected $defaultStartOptions = [
		'name' => 'rise_session',
		'cookie_httponly' => true,
	];

	/**
	 * @var array
	 */
	protected $startOptions = [];

	/**
	 * @var string
	 */
	protected $flashSessionKey = '__flash';

	/**
	 * @var string
	 */
	protected $nextFlashSessionKey = '__nextFlash';

	/**
	 * @var string
	 */
	protected $csrfToken = '';

	/**
	 * @var string
	 */
	protected $csrfTokenSessionKey = '__csrf';

	/**
	 * @var string
	 */
	protected $csrfTokenFormKey = '_csrf';

	/**
	 * @var string
	 */
	protected $csrfTokenHeaderKey = 'X-CSRF';

	/**
	 * @var \Rise\Path
	 */
	protected $path;

	public function __construct(Path $path) {
		$this->path = $path;

		$this->readConfig();
	}

	/**
	 * @return self
	 */
	public function start() {
		if ($this->enabled && $this->started) {
			return $this;
		}

		$options = $this->startOptions + $this->defaultStartOptions;
		session_start($options);
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
	 * Regnerate session ID and CSRF token.
	 *
	 * @return self
	 */
	public function regenerate() {
		if (!$this->started) {
			return $this;
		}

		session_regenerate_id();
		$this->generateCsrfToken();

		return $this;
	}

	/**
	 * Get data from the current flash messages bag.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getFlash($key) {
		return $this->started && isset($_SESSION[$this->flashSessionKey][$key])
			? $_SESSION[$this->flashSessionKey][$key]
			: null;
	}

	/**
	 * Set data to the flash messages bag for the next request.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return self
	 */
	public function setFlash($key, $value) {
		if (!$this->started) {
			return $this;
		}

		$_SESSION[$this->nextFlashSessionKey][$key] = $value;
		return $this;
	}

	/**
	 * Keep flash message for the next request.
	 *
	 * @param string $key
	 * @return self
	 */
	public function keepFlash($key) {
		return $this->setFlash($key, $this->getFlash($key));
	}

	/**
	 * Copy the next flash data to current flash. This should be done in the end of request.
	 *
	 * @return self
	 */
	public function toNextFlash() {
		if (!$this->started) {
			return $this;
		}

		$_SESSION[$this->flashSessionKey] =
			isset($_SESSION[$this->nextFlashSessionKey])
			? $_SESSION[$this->nextFlashSessionKey]
			: [];
		$_SESSION[$this->nextFlashSessionKey] = [];

		return $this;
	}

	/**
	 * Generate CSRF token.
	 *
	 * @return string
	 */
	public function generateCsrfToken() {
		$token = hash("sha512", mt_rand(0, mt_getrandmax()));
		$_SESSION[$this->csrfTokenSessionKey] = $token;
		return $token;
	}

	/**
	 * Get CSRF token.
	 *
	 * @return string
	 */
	public function getCsrfToken() {
		if (!isset($_SESSION[$this->csrfTokenSessionKey])) {
			$this->generateCsrfToken();
		}
		return $_SESSION[$this->csrfTokenSessionKey];
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
	 * Get CSRF token header key.
	 *
	 * @return string
	 */
	public function getCsrfTokenHeaderKey() {
		return $this->csrfTokenHeaderKey;
	}

	/**
	 * Validate with the token stored in session.
	 *
	 * @param string $token
	 * @return bool
	 */
	public function validateCsrfToken($token) {
		return (
			isset($_SESSION[$this->csrfTokenSessionKey])
			&& $_SESSION[$this->csrfTokenSessionKey] === $token
		);
	}

	/**
	 * Generate HTML.
	 *
	 * @return string
	 */
	public function generateCsrfHtml() {
		return '<input type="hidden" name="' . $this->csrfTokenFormKey . '" value="' . $this->getCsrfToken() . '">';
	}

	/**
	 * Generate meta HTML.
	 *
	 * @return string
	 */
	public function generateCsrfMeta() {
		return '<meta name="csrf-header" content="' . $this->csrfTokenHeaderKey . '">' .
			'<meta name="csrf-token" content="' . $this->getCsrfToken() . '">';
	}

	/**
	 * Read configuration file.
	 */
	protected function readConfig() {
		$file = $this->path->getConfigPath() . '/session.php';

		if (file_exists($file)) {
			$config = require($file);
			$this->enabled = true;

			if (isset($config['options']) && is_array($config['options'])) {
				$this->startOptions = $config['options'];
			}
		}
	}
}
