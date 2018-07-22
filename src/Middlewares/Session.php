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
			$token = $_POST[$this->sessionService->getCsrfTokenFormKey()] ?? null;
			break;
		case 'PUT':
			$token = $this->request->getPutParams()[$this->sessionService->getCsrfTokenFormKey()] ?? null;
			break;
		case 'DELETE':
			$token = $this->request->getDeleteParams()[$this->sessionService->getCsrfTokenFormKey()] ?? null;
			break;
		default:
			$next();
			return;
		}

		// Get token from header if it is not found in body.
		if (!$token) {
			$token = $this->request->getHeader($this->sessionService->getCsrfTokenHeaderKey());
		}

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
