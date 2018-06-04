<?php
namespace Rise;

class Initializer {
	/**
	 * @var \Rise\Path
	 */
	protected $path;

	/**
	 * @var \Rise\Database
	 */
	protected $database;

	/**
	 * @var \Rise\Session
	 */
	protected $session;

	/**
	 * @var \Rise\Locale
	 */
	protected $locale;

	/**
	 * @var \Rise\Router
	 */
	protected $router;

	/**
	 * @var \Rise\Dispatcher
	 */
	protected $dispatcher;

	public function __construct(
		Path $path,
		Database $database,
		Session $session,
		Locale $locale,
		Router $router,
		Dispatcher $dispatcher
	) {
		$this->path = $path;
		$this->database = $database;
		$this->session = $session;
		$this->locale = $locale;
		$this->router = $router;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * @param string $projectRootPath
	 * @return self
	 */
	public function setProjectRootPath($projectRootPath) {
		$this->path->setProjectRootPath($projectRootPath);
		return $this;
	}

	/**
	 * @return self
	 */
	public function run() {
		$this->database->readConfigurations();
		$this->session->readConfigurations();
		$this->locale->readConfigurations();
		$this->router->readConfigurations();
		$this->dispatcher->readConfigurations();

		$this->locale->parseRequestLocale();
		$this->router->buildRoutes();
		$this->dispatcher->dispatch();

		return $this;
	}
}
