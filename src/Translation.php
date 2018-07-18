<?php
namespace Rise;

class Translation {
	/**
	 * Current locale
	 *
	 * @var string
	 */
	protected $locale = '';

	/**
	 * Default locale
	 *
	 * @var string
	 */
	protected $defaultLocale = '';

	/**
	 * Format: [
	 *     '<locale code>' => [
	 *         '<key1>' => '<value1>',
	 *         '<key2>' => '<value2>',
	 *         ...
	 *     ],
	 *     ...
	 * ]
	 *
	 * @var array
	 */
	protected $translations = [];

	/**
	 * @var \Rise\Path
	 */
	protected $path;

	public function __construct(Path $path) {
		$this->path = $path;

		$this->readConfig();
	}

	/**
	 * Get locale code of current request.
	 *
	 * @return string
	 */
	public function getLocale() {
		if ($this->locale) {
			return $this->locale;
		}
		return $this->getDefaultLocale();
	}

	/**
	 * @param string $locale
	 * @return self
	 */
	public function setLocale($locale) {
		if (isset($this->translations[$locale])) {
			$this->locale = $locale;
		}
		return $this;
	}

	/**
	 * @param string $locale
	 * @return bool
	 */
	public function hasLocale($locale) {
		return isset($this->translations[$locale]);
	}

	/**
	 * @return string
	 */
	public function getDefaultLocale() {
		return $this->defaultLocale;
	}

	/**
	 * @param string $locale
	 * @return self
	 */
	public function setDefaultLocale($locale) {
		if (isset($this->translations[$locale])) {
			$this->defaultLocale = $locale;
		}
		return $this;
	}

	/**
	 * Translate an identifier to specific value.
	 *
	 * @param string $key Translation identifier.
	 * @param string $defaultValue Optional.
	 * @param string $localeCode Optional. Specify the locale of translation result.
	 * @return string
	 */
	public function translate($key, $defaultValue = '', $locale = null) {
		if ($locale === null) {
			$locale = $this->getlocale();
		}

		if (isset($this->translations[$locale][$key])) {
			return $this->translations[$locale][$key];
		}

		return $defaultValue;
	}

	/**
	 * Read configurations.
	 *
	 * @return self
	 */
	protected function readConfig() {
		$file = $this->path->getConfigPath() . '/translation.php';
		if (file_exists($file)) {
			$config = require($file);
			if (isset($config['translations'])) {
				$this->translations = $config['translations'];
			}
			if (isset($config['defaultLocale'])) {
				$this->setDefaultLocale($config['defaultLocale']);
			}
		}
		return $this;
	}
}
