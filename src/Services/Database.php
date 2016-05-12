<?php
/**
 * Copyright (c) Jack Wan <hwguyguy@gmail.com> - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Jack Wan <hwguyguy@gmail.com>, August 2015
 */
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
	protected $configurations;

	/**
	 * @var \Doctrine\DBAL\Connection[]
	 */
	protected $connections = [];

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
		$this->setConfigurations(require(service('path')->getConfigurationsPath() . '/database.php'));
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
