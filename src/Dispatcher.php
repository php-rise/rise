<?php
namespace Rise;

use Rise\Http\Response;
use Rise\Container\DynamicFactory;

class Dispatcher {
	/**
	 * @var string
	 */
	protected $handlerNamespace = '';

	/**
	 * @var \Rise\Path
	 */
	protected $path;

	/**
	 * @var \Rise\Router
	 */
	protected $router;

	/**
	 * @var \Rise\Http\Response
	 */
	protected $response;

	/**
	 * @var \Rise\Session
	 */
	protected $session;

	/**
	 * @var \Rise\Factories\Container\DynamicFactory
	 */
	protected $dynamicFactory;

	public function __construct(
		Path $path,
		Router $router,
		Response $response,
		Session $session,
		DynamicFactory $dynamicFactory
	) {
		$this->path = $path;
		$this->router = $router;
		$this->response = $response;
		$this->session = $session;
		$this->dynamicFactory = $dynamicFactory;

		$this->readConfigurations();
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
			$this->response->send();
			$this->session->clearFlash()
				->rememberCsrfToken();
		} else {
			$matchedHandler = $this->router->getMatchedHandler();
			if ($matchedHandler) {
				$this->getHandlerResult($matchedHandler);
			}
			$this->response
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
			return !($instance->{$method}() === false);
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
