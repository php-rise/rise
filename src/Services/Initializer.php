<?php
namespace Rise\Services;

class Initializer extends BaseService {
	/**
	 * @param string $projectRootPath
	 * @return self
	 */
	public function setProjectRootPath($projectRootPath) {
		service()->setService('path', new Path);
		service('path')->setProjectRootPath($projectRootPath);
		return $this;
	}

	/**
	 * @return self
	 */
	public function readConfigurations() {
		$configurations = require(service('path')->getConfigurationsPath() . '/initializer.php');
		if (isset($configurations['serviceNamespaces'])) {
			foreach ((array)$configurations['serviceNamespaces'] as $namespace) {
				service()->addNamespace($namespace);
			}
		}
		return $this;
	}

	public function registerServices() {
		service()
			->setService('http', new Http)
			->setService('http/upload', 'Http\Upload')
			->setService('database', new Database)
			->setService('locale', new Locale)
			->setService('session', new Session)
			->setService('router', new Router)
			->setService('dispatcher', new Dispatcher)
			->setService('template', new Template);
	}

	public function run() {
		$this->readConfigurations();
		$this->registerServices();

		service('database')->readConfigurations();
		service('session')->initialize();
		service('locale')->readConfigurations();
		service('router')->readConfigurations();
		service('dispatcher')->readConfigurations();

		service('locale')->parseRequestLocale();
		service('router')->buildRoutes();
		service('dispatcher')->dispatch();
	}
}
