<?php
namespace Rise\Services;

class Locale extends BaseService {
	const SOURCE_FILE = 1;
	const SOURCE_DATABASE = 2;

	/**
	 * Indicate where the locale configurations come from.
	 *
	 * @var int
	 */
	protected $source = 2;

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
	 * Initialize locale service.
	 *
	 * Read configurations and set current locale.
	 *
	 * @return self
	 */
	public function initialize() {
		$this->readConfigurations()
			->parseRequestLocale();
		return $this;
	}

	/**
	 * Read configurations from source.
	 *
	 * @return self
	 */
	public function readConfigurations() {
		switch ($this->source) {
		case static::SOURCE_FILE:
			$this->readConfigurationsFromFile();
			break;
		case static::SOURCE_DATABASE:
			$this->readConfigurationsFromDatabase();
			break;
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
	 *
	 * @param string $key
	 * @param string $localeCode Optional. Specify the locale of translation result.
	 * @return string
	 */
	public function translate($key = '', $localeCode = null) {
		$keys = explode('.', $key);
	}

	/**
	 * @TODO
	 *
	 * @return self
	 */
	protected function readConfigurationsFromFile() {
		return $this;
	}

	/**
	 * @return self
	 */
	protected function readConfigurationsFromDatabase() {
		$statement = service('database')->getQueryBuilder()
			->select('*')
			->from('locale')
			->execute();
		$locales = [];
		while ($record = $statement->fetch()) {
			if ($record['enabled']) {
				$locales[$record['code']] = $record;
			}
		}
		$this->locales = $locales;
		return $this;
	}
}
