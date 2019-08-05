<?php
namespace Rise\Middlewares;

use Closure;
use Exception;
use Rise\Response as ResponseService;
use Rise\Router\RouteNotFoundException;

class Response {
	/**
	 * @var \Rise\Response
	 */
	protected $response;

	public function __construct(ResponseService $response) {
		$this->response = $response;
	}

	public function run(Closure $next) {
		try {
			$next();
		} catch (RouteNotFoundException $e) {
			$this->response->setStatusCode(ResponseService::HTTP_NOT_FOUND);
		} catch (Exception $e) {
			$this->response->setStatusCode(ResponseService::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response->send()->end();
	}
}
