<?php
namespace Rise;

use Rise\Dispatcher\HandlerFactory;

class Dispatcher {
	/**
	 * Handlers / middlewares
	 *
	 * @var array
	 */
	protected $handlers = [];

	/**
	 * @var \Rise\Dispatcher\HandlerFactory
	 */
	protected $handlerFactory;

	/**
	 * @var \Rise\Path
	 */
	protected $path;

	public function __construct(HandlerFactory $handlerFactory, Path $path) {
		$this->handlerFactory = $handlerFactory;
		$this->path = $path;
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
	 * Read configuration file and set handlers.
	 *
	 * @return self
	 */
	public function readConfig() {
		$file = $this->path->getConfigPath() . '/dispatcher.php';

		if (file_exists($file)) {
			$config = require($file);

			if (isset($config['middlewares']) && is_array($config['middlewares'])) {
				$this->setHandlers($config['middlewares']);
			}
		}

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
