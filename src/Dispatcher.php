<?php
namespace Rise;

use Rise\Dispatcher\HandlerFactory;

class Dispatcher {
	/**
	 * @var array
	 */
	protected $handlers = [];

	/**
	 * @var \Rise\Dispatcher\HandlerFactory
	 */
	protected $handlerFactory;

	public function __construct(HandlerFactory $handlerFactory) {
		$this->handlerFactory = $handlerFactory;
	}

	/**
	 * Dispatch handlers.
	 *
	 * @return self
	 */
	public function dispatch() {
		$handler = current($this->handlers);
		if (!$handler) {
			return;
		}

		$this->resolveHandler($handler);

		return $this;
	}

	/**
	 * Set handlers.
	 *
	 * @param string|array $handlers
	 * return self
	 */
	public function setHandlers($handlers) {
		$this->handlers = (array)$handlers;
		return $this;
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
