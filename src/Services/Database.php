<?php
namespace Rise\Services;

use Doctrine\DBAL\DriverManager as DbalManager;
use Doctrine\DBAL\Configuration as DbalConfiguration;

class Database extends BaseService {
	/**
	 * @var bool
	 */
	protected $initialized = false;

	/**
	 * @var array
	 */
	protected $configurations = [];

	/**
	 * @var \Doctrine\DBAL\Connection[]
	 */
	protected $connections = [];

	/**
	 * @var \Rise\Services\Path
	 */
	protected $path;

	public function __construct(Path $path) {
		$this->path = $path;
	}

	/**
	 * @return array
	 */
	public function getConfigurations() {
		return $this->configurations;
	}

	/**
	 * @param array $configurations
	 * @return self
	 */
	public function setConfigurations($configurations = []) {
		$this->configurations = $configurations;
		return $this;
	}

	/**
	 * Read configuration file.
	 *
	 * @return self
	 */
	public function readConfigurations() {
		$configurationFile = $this->path->getConfigurationsPath() . '/database.php';
		if (file_exists($configurationFile)) {
			$this->setConfigurations(require($configurationFile));
		}
		return $this;
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
	 * Get a connection by name.
	 *
	 * @param string $name Optional. Connection name.
	 * @return \Doctrine\DBAL\Connection|null
	 */
	public function getConnection($name = 'default') {
		if (isset($this->connections[$name])) {
			return $this->connections[$name];
		}

		if (isset($this->configurations[$name])) {
			$configuration = $this->configurations[$name];
		} else {
			return null;
		}

		$connection = DbalManager::getConnection($configuration, new DbalConfiguration);
		$this->connections[$name] = $connection;
		return $connection;
	}

	/**
	 * @param string $name Optional. Connection name.
	 * @return \Doctrine\DBAL\Query\QueryBuilder
	 */
	public function getQueryBuilder($name = 'default') {
		return $this->getConnection($name)->createQueryBuilder();
	}
}
