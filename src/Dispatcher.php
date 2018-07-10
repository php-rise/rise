<?php
namespace Rise;

use Rise\Response;
use Rise\Dispatcher\HandlerFactory;

class Dispatcher {
	/**
	 * @var array
	 */
	protected $handlers = [];

	/**
	 * @var \Rise\Router
	 */
	protected $router;

	/**
	 * @var \Rise\Response
	 */
	protected $response;

	/**
	 * @var \Rise\Dispatcher\HandlerFactory
	 */
	protected $handlerFactory;

	public function __construct(
		Router $router,
		Response $response,
		HandlerFactory $handlerFactory
	) {
		$this->router = $router;
		$this->response = $response;
		$this->handlerFactory = $handlerFactory;
	}

	/**
	 * Dispatch current request and send response.
	 *
	 * @return self
	 */
	public function dispatch() {
		$this->router->match();
		$this->response->setStatusCode($this->router->getMatchedStatus());
		$this->setHandlers($this->router->getMatchedHandler());
		$this->runHandlers();
		$this->response->send();
		return $this;
	}

	/**
	 * @param string|array $handlers
	 */
	protected function setHandlers($handlers) {
		$this->handlers = (array)$handlers;
	}

	protected function runHandlers() {
		$handler = current($this->handlers);
		if (!$handler) {
			return;
		}

		$this->resolveHandler($handler);
	}

	/**
	 * @param string $handler
	 * @return mixed
	 */
	private function resolveHandler($handler) {
		$next = $this->getNext();
		list($instance, $method, $args) = $this->handlerFactory->create($handler, $next);
		return $instance->{$method}(...$args);
	}

	/**
	 * @return \Closure
	 */
	private function getNext() {
		$handler = next($this->handlers);
		if (!$handler) {
			return function () {}; // Return a dummy middleware
		}

		return function () use ($handler) {
			return $this->resolveHandler($handler);
		};
	}
}
