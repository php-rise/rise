<?php
namespace Rise\Services;

class Initializer extends BaseService {
	/**
	 * @var \Rise\Services\Path
	 */
	protected $path;

	/**
	 * @var \Rise\Services\Database
	 */
	protected $database;

	/**
	 * @var \Rise\Services\Session
	 */
	protected $session;

	/**
	 * @var \Rise\Services\Locale
	 */
	protected $locale;

	/**
	 * @var \Rise\Services\Router
	 */
	protected $router;

	/**
	 * @var \Rise\Services\Dispatcher
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
