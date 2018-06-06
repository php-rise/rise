<?php
namespace Rise;

use Rise\Http\Request;

class Locale {
	/**
	 *
	 *
	 * Format: [
	 *     '<locale code>' => [
	 *         'name' => '<locale name>',
	 *     ],
	 *     ...
	 * ]
	 *
	 * @var array
	 */
	protected $locales = [];

	/**
	 * @var string
	 */
	protected $currentLocaleCode = '';

	/**
	 * @var string
	 */
	protected $defaultLocaleCode = '';

	/**
	 * Format: [
	 *     '<locale code>' => [
	 *         '<key1>' => '<value1>',
	 *         '<key2>' => [
	 *             '<nested key>' => '<value2>',
	 *             ...
	 *         ],
	 *         ...
	 *     ],
	 *     ...
	 * ]
	 *
	 * @var array
	 */
	protected $translations = [
		'en' => [
		],
	];

	/**
	 * @var \Rise\Path
	 */
	protected $path;

	/**
	 * @var \Rise\Http\Request
	 */
	protected $request;

	public function __construct(Path $path, Request $request) {
		$this->path = $path;
		$this->request = $request;
	}

	/**
	 *
	 *
	 * @return array
	 */
	public function getLocales() {
		return $this->locales;
	}

	/**
	 * @return self
	 */
	public function setLocales($locales) {
		$this->locales = $locales;
		return $this;
	}

	/**
	 * Get locale code of current request.
	 *
	 * @return string
	 */
	public function getCurrentLocaleCode() {
		if ($this->currentLocaleCode) {
			return $this->currentLocaleCode;
		}
		return $this->getDefaultLocaleCode();
	}

	/**
	 * @return string
	 */
	public function getDefaultLocaleCode() {
		return $this->defaultLocaleCode;
	}

	/**
	 * @return self
	 */
	public function setDefaultLocaleCode($defaultLocaleCode) {
		$this->defaultLocaleCode = $defaultLocaleCode;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getTranslations() {
		return $this->translations;
	}

	/**
	 * @param array $translations
	 * @return self
	 */
	public function setTranslations($translations) {
		$this->translations = $translations;
		return $this;
	}

	/**
	 * Read configurations.
	 *
	 * @return self
	 */
	public function readConfigurations() {
		$file = $this->path->getConfigurationsPath() . '/locale.php';
		if (file_exists($file)) {
			$configurations = require($file);
			if (isset($configurations['locales'])) {
				$this->locales = $configurations['locales'];
			}
			if (isset($configurations['defaultLocaleCode'])) {
				$this->defaultLocaleCode = $configurations['defaultLocaleCode'];
			}
			if (isset($configurations['translations'])) {
				$this->translations = $configurations['translations'];
			}
		}
		return $this;
	}

	/**
	 * Set current locale code and appropriate request path after checking the locale code in URL.
	 *
	 * @return self
	 */
	public function parseRequestLocale() {
		$request = $this->request;
		list(, $localeCode, $requestPath) = array_pad(explode('/', $request->getRequestUri(), 3), 3, null);
		if (isset($this->locales[$localeCode])) {
			$this->currentLocaleCode = $localeCode;
			$request->setRequestPath('/' . $requestPath);
		} else {
			$request->setRequestPath($request->getRequestUri());
		}
		return $this;
	}

	/**
	 * Translate an identifier to specific value.
	 *
	 * @param string $key Translation identifier separate by ".".
	 * @param string $defaultValue Optional.
	 * @param string $localeCode Optional. Specify the locale of translation result.
	 * @return string
	 */
	public function translate($key = '', $defaultValue = '', $localeCode = null) {
		if ($localeCode === null) {
			$localeCode = $this->getCurrentLocaleCode();
		}

		if (!$localeCode) {
			return $defaultValue;
		}

		if (!isset($this->translations[$localeCode])) {
			return $defaultValue;
		}

		$translationReference = &$this->translations[$localeCode];
		$keys = explode('.', $key);
		foreach ($keys as $_key) {
			if (!is_array($translationReference)
				|| !isset($translationReference[$_key])
			) {
				return $defaultValue;
			}
			$translationReference = &$translationReference[$_key];
		}

		if (is_string($translationReference)) {
			return $translationReference;
		}

		return $defaultValue;
	}
}
