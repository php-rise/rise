<?php
namespace Rise;

use PDO;

class Database {
	/**
	 * @var string
	 */
	protected $defaultConfigName = '';

	/**
	 * @var array
	 */
	protected $connectionConfigs = [];

	/**
	 * Associated array of PDO instances
	 * @var array
	 */
	protected $connections = [];

	/**
	 * @var \Rise\Path
	 */
	protected $path;

	public function __construct(Path $path) {
		$this->path = $path;
		$this->readConfig();
	}

	/**
	 * Get all connection configs.
	 *
	 * @return array
	 */
	public function getConnectionConfigs() {
		return $this->connectionConfigs;
	}

	/**
	 * Get connection config.
	 *
	 * @param string $name
	 * @return array|null
	 */
	public function getConnectionConfig($name = null) {
		if (is_null($name)) {
			$name = $this->defaultConfigName;
		}

		if (isset($this->connectionConfigs[$name])) {
			return $this->connectionConfigs[$name];
		}

		return null;
	}

	/**
	 * Set connection config. This will overwrite existing config with the same name.
	 *
	 * @param string $name
	 * @param array $config
	 * @return self
	 */
	public function setConnectionConfig($name, $config) {
		$this->connectionConfigs[$name] = $config;
		return $this;
	}

	/**
	 * Get a connection by name.
	 *
	 * @param string $name Optional. Connection name.
	 * @param bool $forceNew Optional. Create a new connection or reuse the old one if exists.
	 * @return \PDO|null
	 */
	public function getConnection($name = null, $forceNew = false) {
		if (!$forceNew && isset($this->connections[$name])) {
			return $this->connections[$name];
		}

		$config = $this->getConnectionConfig($name);

		if (!$config) {
			return null;
		}

		$pdoArgs = [$config['dsn'], $config['username'], $config['password']];
		$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

		if (isset($config['options'])) {
			$options = $config['options'] + $options;
		}
		$pdoArgs[] = $options;

		$this->connections[$name] = new PDO(...$pdoArgs);

		return $this->connections[$name];
	}

	/**
	 * Clear all connections.
	 *
	 * This is useful in commands after changing some database configurations.
	 *
	 * @return self
	 */
	public function clearConnections() {
		$this->connections = [];
		return $this;
	}

	/**
	 * Read configuration file.
	 */
	public function readConfig() {
		$file = $this->path->getConfigPath() . '/database.php';
		if (file_exists($file)) {
			$config = require($file);
			$this->defaultConfigName = $config['default'];
			$this->connectionConfigs = $config['connections'];
		}
	}
}
