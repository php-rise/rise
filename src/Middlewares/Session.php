<?php
namespace Rise\Middlewares;

use Closure;
use Rise\Session as SessionService;
use Rise\Http\Request;
use Rise\Http\Response;

class Session {
	/**
	 * @var \Rise\Session
	 */
	protected $sessionService;

	/**
	 * @var \Rise\Http\Request
	 */
	protected $request;

	/**
	 * @var \Rise\Http\Response
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
		$request = $this->request;
		if ($request->isMethod('POST')
			|| $request->isMethod('PUT')
			|| $request->isMethod('DELETE')
		) {
			$token = $request->get($this->sessionService->getCsrfTokenFormKey());
			if (!$this->sessionService->validateCsrfToken($token)) {
				$response = $this->response;
				$response->setStatusCode(403);
				$response->setContentType('text/plain');
				$response->setBody('Invalid CSRF token');
				return;
			}
		}
		$next();
	}
}
