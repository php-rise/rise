<?php
namespace Rise;

class Path {
	/**
	 * Root path of the application.
	 *
	 * @var string
	 */
	protected $projectRootPath;

	/**
	 * Path of the directory storing configurations.
	 *
	 * @var string
	 */
	protected $configurationsPath;

	/**
	 * Path of the public directory.
	 *
	 * @var string
	 */
	protected $publicPath;

	/**
	 * Path of the log directory.
	 *
	 * @var string
	 */
	protected $logsPath;

	/**
	 * Path of the templates directory.
	 *
	 * @var string
	 */
	protected $templatesPath;

	/**
	 * Path of the directory storing sessions.
	 *
	 * @var string
	 */
	protected $sessionsPath;

	/**
	 * Path of the directory storing migration files.
	 *
	 * @var string
	 */
	protected $migrationsPath;

	/**
	 * @return string
	 */
	public function getProjectRootPath() {
		return $this->projectRootPath;
	}

	/**
	 * @param string $projectRootPath
	 * @return self
	 */
	public function setProjectRootPath($projectRootPath) {
		$this->projectRootPath = realpath($projectRootPath);
		$this->configurationsPath = $this->projectRootPath . '/config';

		$this->publicPath = $this->projectRootPath . '/public';
		$this->logsPath = $this->projectRootPath . '/logs';
		$this->templatesPath = $this->projectRootPath . '/templates';
		$this->sessionsPath = $this->projectRootPath . '/sessions';
		$this->migrationsPath = $this->projectRootPath . '/migrations';

		return $this;
	}

	public function getConfigurationsPath() {
		return $this->configurationsPath;
	}

	public function getPublicPath() {
		return $this->publicPath;
	}

	public function getLogsPath() {
		return $this->logsPath;
	}

	public function getTemplatesPath() {
		return $this->templatesPath;
	}

	public function getSessionsPath() {
		return $this->sessionsPath;
	}

	public function getMigrationsPath() {
		return $this->migrationsPath;
	}
}
