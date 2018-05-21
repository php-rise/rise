<?php
namespace Rise\Services\Http;

use Rise\Services\BaseService;
use Rise\Services\Router;
use Rise\Services\Template;
use Rise\Factories\Http\ResponseFactory;

class Responder extends BaseService {
	/**
	 * @var \Rise\Components\Http\Response|null
	 */
	protected $response = null;

	/**
	 * @var \Rise\Services\Http\Receiver
	 */
	protected $receiver;

	/**
	 * @var \Rise\Services\Router
	 */
	protected $router;

	/**
	 * @var \Rise\Services\Template
	 */
	protected $template;

	/**
	 * @var \Rise\Factories\Http\ResponseFactory
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
<html>
	<head>
		<meta charset="UTF-8" />
		<meta http-equiv="refresh" content="1;url=%1$s" />
		<title>Redirecting to %1$s</title>
	</head>
	<body>
		Redirecting to <a href="%1$s">%1$s</a>.
	</body>
</html>', htmlspecialchars($url, ENT_QUOTES, 'UTF-8')));

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
	 * @return \Rise\Components\Http\Response
	 */
	public function getResponse() {
		return $this->response ? $this->response : $this->createResponse();
	}

	/**
	 * @return \Rise\Components\Http\Response
	 */
	private function createResponse() {
		$request = $this->receiver->getRequest();
		$this->response = $this->responseFactory->create()->setRequest($request);
		return $this->response;
	}
}
