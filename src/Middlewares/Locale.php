<?php
namespace Rise\Middlewares;

use Closure;
use Rise\Request;
use Rise\Response;
use Rise\Translation;

class Locale {
	/**
	 * @var \Rise\Request
	 */
	protected $request;

	/**
	 * @var \Rise\Response
	 */
	protected $response;

	/**
	 * @var \Rise\Translation
	 */
	protected $translation;

	public function __construct(
		Request $request,
		Response $response,
		Translation $translation
	) {
		$this->request = $request;
		$this->response = $response;
		$this->translation = $translation;
	}

	/**
	 * Get locale from request url.
	 * To use it, you should put "{locale}" in the routes config.
	 *
	 * e.g.: $scope->get('/{locale}/products', 'App\Handlers\Handler');
	 *       or
	 *       $scope->prefix('/{locale}');
	 *
	 * @param \Closure $next
	 */
	public function extractFromPath(Closure $next) {
		$locale = $this->request->getParam('locale');
		if ($this->translation->hasLocale($locale)) {
			$this->translation->setLocale($locale);
			$next();
		} else {
			$this->notFound();
		}
	}

	public function extractFromTld(Closure $next) {
		$host = $this->request->getHost();

		if ($host) {
			$parts = explode('.', $host);
			$locale = end($parts);
		}

		if (isset($locale) && $this->translation->hasLocale($locale)) {
			$this->translation->setLocale($locale);
			$next();
		} else {
			$this->notFound();
		}
	}

	public function extractFromSubdomain(Closure $next) {
		$host = $this->request->getHost();

		if ($host) {
			$parts = explode('.', $host);
			if (sizeof($parts) > 2) {
				$locale = reset($parts);
			}
		}

		if (isset($locale) && $this->translation->hasLocale($locale)) {
			$this->translation->setLocale($locale);
			$next();
		} else {
			$this->notFound();
		}
	}

	protected function notFound() {
		$response = $this->response;
		$response->setStatusCode(404);
		$response->setHeader('Content-Type', 'text/plain');
		$response->setBody('Invalid locale');
	}
}
