<?php
namespace Rise\Services;

use Rise\Components\Http\Request;
use Rise\Components\Http\Response;

class Http extends BaseService {
	/**
	 * @var \Rise\Components\Http\Request|null
	 */
	protected $request = null;

	/**
	 * @var \Rise\Components\Http\Response|null
	 */
	protected $response = null;

	/**
	 * Setup HTTP response for a HTML page.
	 *
	 * @param string $template
	 * @param array $data optional
	 * @return self
	 */
	public function html($template = '', $data = []) {
		$body = service('template')->renderPage($template, $data);
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
		$this->redirect(service('router')->generateUrl($name, $params));
		return $this;
	}

	/**
	 * @return \Rise\Components\Http\Request
	 */
	public function getRequest() {
		return $this->request ? $this->request : $this->createRequest();
	}

	/**
	 * @return \Rise\Components\Http\Response
	 */
	public function getResponse() {
		return $this->response ? $this->response : $this->createResponse();
	}

	/**
	 * @return \Rise\Components\Http\Request
	 */
	protected function createRequest() {
		$this->request = (new Request)->setMethod($_SERVER['REQUEST_METHOD'])
			->setRequestUri($_SERVER['REQUEST_URI']);
		return $this->request;
	}

	/**
	 * @return \Rise\Components\Http\Response
	 */
	protected function createResponse() {
		$this->response = (new Response)->setRequest($this->getRequest());
		return $this->response;
	}
}
