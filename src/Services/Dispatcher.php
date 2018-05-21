<?php
namespace Rise\Services;

use Rise\Services\Http\Responder;
use Rise\Factories\Container\DynamicFactory;

class Dispatcher extends BaseService {
	/**
	 * @var string
	 */
	protected $handlerNamespace = '';

	/**
	 * @var \Rise\Services\Path
	 */
	protected $path;

	/**
	 * @var \Rise\Services\Router
	 */
	protected $router;

	/**
	 * @var \Rise\Services\Http\Responder
	 */
	protected $responder;

	/**
	 * @var \Rise\Services\Session
	 */
	protected $session;

	/**
	 * @var \Rise\Factories\Container\DynamicFactory
	 */
	protected $dynamicFactory;

	public function __construct(
		Path $path,
		Router $router,
		Responder $responder,
		Session $session,
		DynamicFactory $dynamicFactory
	) {
		$this->path = $path;
		$this->router = $router;
		$this->responder = $responder;
		$this->session = $session;
		$this->dynamicFactory = $dynamicFactory;
	}

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
		$configurations = require($this->path->getConfigurationsPath() . '/dispatcher.php');
		$this->setHandlerNamespace($configurations['handlerNamespace']);
		return $this;
	}

	/**
	 * Dispatch current request and send response.
	 *
	 * @return self
	 */
	public function dispatch() {
		if ($this->router->match()) {
			$this->session->toggleCurrentFlashBagKey();
			$this->getHandlerResult($this->router->getMatchedHandler());
			$this->responder->getResponse()->send();
			$this->session->clearFlash()
				->rememberCsrfToken();
		} else {
			$this->responder->getResponse()
				->setStatusCode($this->router->getMatchedStatus())
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
			$instance = $this->dynamicFactory->create($class);
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
