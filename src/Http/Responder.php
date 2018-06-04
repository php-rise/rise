<?php
namespace Rise\Http;

use Rise\Router;
use Rise\Template;
use Rise\Http\Responder\ResponseFactory;

class Responder {
	/**
	 * @var \Rise\Http\Responder\Response|null
	 */
	protected $response = null;

	/**
	 * @var \Rise\Http\Receiver
	 */
	protected $receiver;

	/**
	 * @var \Rise\Router
	 */
	protected $router;

	/**
	 * @var \Rise\Template
	 */
	protected $template;

	/**
	 * @var \Rise\Http\Responder\ResponseFactory
	 */
	protected $responseFactory;

	public function __construct(
		Receiver $receiver,
		Router $router,
		Template $template,
		ResponseFactory $responseFactory
	) {
		$this->receiver = $receiver;
		$this->router = $router;
		$this->template = $template;
		$this->responseFactory = $responseFactory;
	}

	/**
	 * Setup HTTP response for a HTML page.
	 *
	 * @param string $template
	 * @param array $data optional
	 * @return self
	 */
	public function html($template = '', $data = []) {
		$body = $this->template->renderPage($template, $data);
		$this->getResponse()->setBody($body);
		return $this;
	}

	/**
	 * Setup HTTP response for JSON.
	 *
	 * @param array $data
	 * @return self
	 */
	public function json($data = []) {
		$this->getResponse()
			->setHeader('Content-Type', 'application/json')
			->setBody(json_encode($data));
		return $this;
	}

	/**
	 * Setup HTTP redirect.
	 *
	 * @param string $url
	 * @param bool $permanent optional
	 * @return self
	 */
	public function redirect($url, $permanent = false) {
		$statusCode = $permanent ? 301 : 302;
		$this->getResponse()
			->setStatusCode($statusCode)
			->setHeader('Location', $url)
			->setBody(sprintf('<!DOCTYPE html>
<meta charset="UTF-8">
<meta http-equiv="refresh" content="1;url=%1$s">
<title>Redirecting to %1$s</title>
Redirecting to <a href="%1$s">%1$s</a>.', htmlspecialchars($url, ENT_QUOTES, 'UTF-8')));

		return $this;
	}

	/**
	 * HTTP redirect to a named route.
	 *
	 * @param string $routeName
	 * @param array $params
	 * @return self
	 */
	public function redirectRoute($name = '', $params = []) {
		$this->redirect($this->router->generateUrl($name, $params));
		return $this;
	}

	/**
	 * @return \Rise\Http\Responder\Response
	 */
	public function getResponse() {
		return $this->response ? $this->response : $this->createResponse();
	}

	/**
	 * @return \Rise\Http\Responder\Response
	 */
	private function createResponse() {
		$request = $this->receiver->getRequest();
		$this->response = $this->responseFactory->create()->setRequest($request);
		return $this->response;
	}
}
