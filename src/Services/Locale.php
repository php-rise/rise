<?php
namespace Rise\Services;

class Locale extends BaseService {
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
	 * @var array
	 */
	protected $translations = [
		'en' => [
		],
	];

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
		return $this->currentLocaleCode;
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
		$file = service('path')->getConfigurationsPath() . '/locale.php';
		if (file_exists($file)) {
			$configurations = require($file);
			if (isset($configurations['locales'])) {
				$this->locales = $configurations['locales'];
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
		$request = service('http')->getRequest();
		list(, $localeCode, $requestPath) = array_pad(explode('/', $request->getRequestUri(), 3), 3, null);
		if (isset($this->locales[$localeCode])) {
			$this->currentLocaleCode = $localeCode;
			$request->setRequestPath($requestPath);
		} else {
			$request->setRequestPath($request->getRequestUri());
		}
		return $this;
	}

	/**
	 * Translate an identifier to specific value.
	 * @TODO not complete
	 *
	 * @param string $key
	 * @param string $localeCode Optional. Specify the locale of translation result.
	 * @return string
	 */
	public function translate($key = '', $localeCode = null) {
		$keys = explode('.', $key);
	}
}
