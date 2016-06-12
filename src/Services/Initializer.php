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
		if (isset($configurations['services'])) {
			foreach ($configurations['services'] as $serviceName => $service) {
				service()->setService($serviceName, $service);
			}
		}
		return $this;
	}

	/**
	 * @return self
	 */
	public function registerServices() {
		service()
			->setServiceOnEmpty('http', new Http)
			->setServiceOnEmpty('http/upload', 'Http\Upload')
			->setServiceOnEmpty('database', new Database)
			->setServiceOnEmpty('locale', new Locale)
			->setServiceOnEmpty('session', new Session)
			->setServiceOnEmpty('router', new Router)
			->setServiceOnEmpty('dispatcher', new Dispatcher)
			->setServiceOnEmpty('template', new Template);
		return $this;
	}

	/**
	 * @return self
	 */
	public function run() {
		$this->readConfigurations();
		$this->registerServices();

		service('database')->readConfigurations();
		service('session')->readConfigurations();
		service('locale')->readConfigurations();
		service('router')->readConfigurations();
		service('dispatcher')->readConfigurations();

		service('locale')->parseRequestLocale();
		service('router')->buildRoutes();
		service('dispatcher')->dispatch();

		return $this;
	}
}
