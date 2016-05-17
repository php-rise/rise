<?php
namespace Rise\Services;

class Dispatcher extends BaseService {
	/**
	 * @var string
	 */
	protected $handlerNamespace = '';

	/**
	 * @param string $handlerNamespace
	 * @return self
	 */
	public function setHandlerNamespace($handlerNamespace) {
		$this->handlerNamespace = $handlerNamespace;
		return $this;
	}

	/**
	 * @return self
	 */
	public function readConfigurations() {
		$configurations = require(service('path')->getConfigurationsPath() . '/dispatcher.php');
		$this->setHandlerNamespace($configurations['handlerNamespace']);
		return $this;
	}

	/**
	 * Dispatch current request and send response.
	 *
	 * @return self
	 */
	public function dispatch() {
		$router = service('router');
		if ($router->match()) {
			service('session')->toggleCurrentFlashBagKey();
			$this->getHandlerResult($router->getMatchedHandler());
			service('http')->getResponse()->send();
			service('session')->clearFlash()
				->rememberCsrfToken();
		} else {
			service('http')->getResponse()
				->setStatusCode($router->getMatchedStatus())
				->send();
		}
		return $this;
	}

	/**
	 * @param string|array $handler
	 * @param bool
	 */
	protected function getHandlerResult($handler) {
		if (is_string($handler)) {
			list($class, $method) = explode('.', $handler, 2);
			$class = $this->handlerNamespace . '\\' . $class;
			$instance = new $class();
			if ($instance->{$method}() === false) {
				return false;
			}
			return true;
		}
		if (is_array($handler)) {
			$handlers = $handler;
			foreach ($handlers as $handler) {
				if ($this->getHandlerResult($handler) === false) {
					return false;
				}
			}
			return true;
		}
	}
}
