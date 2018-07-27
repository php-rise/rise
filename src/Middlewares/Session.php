<?php
namespace Rise\Middlewares;

use Closure;
use Rise\Session as SessionService;
use Rise\Request;
use Rise\Response;

class Session {
	/**
	 * @var \Rise\Session
	 */
	protected $sessionService;

	/**
	 * @var \Rise\Request
	 */
	protected $request;

	/**
	 * @var \Rise\Response
	 */
	protected $response;

	public function __construct(
		SessionService $sessionService,
		Request $request,
		Response $response
	) {
		$this->sessionService = $sessionService;
		$this->request = $request;
		$this->response = $response;
	}

	public function run(Closure $next) {
		$this->sessionService->start();
		$next();
		$this->sessionService->toNextFlash();
	}

	public function validateCsrf(Closure $next) {
		switch ($this->request->getMethod()) {
		case 'POST':
		case 'PUT':
		case 'DELETE':
			break;
		default:
			$next();
			return;
		}

		$token = $this->request->getInput()[$this->sessionService->getCsrfTokenFormKey()]
			?? $this->request->getHeader($this->sessionService->getCsrfTokenHeaderKey());

		if (!$token || !$this->sessionService->validateCsrfToken($token)) {
			$response = $this->response;
			$response->setStatusCode(403);
			$response->setHeader('Content-Type', 'text/plain');
			$response->setBody('Invalid CSRF token');
		} else {
			$next();
		}
	}
}
