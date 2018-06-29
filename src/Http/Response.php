<?php
namespace Rise\Http;

use Rise\Template;
use Rise\Router\UrlGenerator;

class Response {
	// @NOTE HTTP status codes from Symfony\Component\HttpFoundation\Response
	const HTTP_CONTINUE = 100;
	const HTTP_SWITCHING_PROTOCOLS = 101;
	const HTTP_PROCESSING = 102;            // RFC2518
	const HTTP_OK = 200;
	const HTTP_CREATED = 201;
	const HTTP_ACCEPTED = 202;
	const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
	const HTTP_NO_CONTENT = 204;
	const HTTP_RESET_CONTENT = 205;
	const HTTP_PARTIAL_CONTENT = 206;
	const HTTP_MULTI_STATUS = 207;          // RFC4918
	const HTTP_ALREADY_REPORTED = 208;      // RFC5842
	const HTTP_IM_USED = 226;               // RFC3229
	const HTTP_MULTIPLE_CHOICES = 300;
	const HTTP_MOVED_PERMANENTLY = 301;
	const HTTP_FOUND = 302;
	const HTTP_SEE_OTHER = 303;
	const HTTP_NOT_MODIFIED = 304;
	const HTTP_USE_PROXY = 305;
	const HTTP_RESERVED = 306;
	const HTTP_TEMPORARY_REDIRECT = 307;
	const HTTP_PERMANENTLY_REDIRECT = 308;  // RFC7238
	const HTTP_BAD_REQUEST = 400;
	const HTTP_UNAUTHORIZED = 401;
	const HTTP_PAYMENT_REQUIRED = 402;
	const HTTP_FORBIDDEN = 403;
	const HTTP_NOT_FOUND = 404;
	const HTTP_METHOD_NOT_ALLOWED = 405;
	const HTTP_NOT_ACCEPTABLE = 406;
	const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
	const HTTP_REQUEST_TIMEOUT = 408;
	const HTTP_CONFLICT = 409;
	const HTTP_GONE = 410;
	const HTTP_LENGTH_REQUIRED = 411;
	const HTTP_PRECONDITION_FAILED = 412;
	const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
	const HTTP_REQUEST_URI_TOO_LONG = 414;
	const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
	const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
	const HTTP_EXPECTATION_FAILED = 417;
	const HTTP_I_AM_A_TEAPOT = 418;                                               // RFC2324
	const HTTP_UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
	const HTTP_LOCKED = 423;                                                      // RFC4918
	const HTTP_FAILED_DEPENDENCY = 424;                                           // RFC4918
	const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425;   // RFC2817
	const HTTP_UPGRADE_REQUIRED = 426;                                            // RFC2817
	const HTTP_PRECONDITION_REQUIRED = 428;                                       // RFC6585
	const HTTP_TOO_MANY_REQUESTS = 429;                                           // RFC6585
	const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;                             // RFC6585
	const HTTP_INTERNAL_SERVER_ERROR = 500;
	const HTTP_NOT_IMPLEMENTED = 501;
	const HTTP_BAD_GATEWAY = 502;
	const HTTP_SERVICE_UNAVAILABLE = 503;
	const HTTP_GATEWAY_TIMEOUT = 504;
	const HTTP_VERSION_NOT_SUPPORTED = 505;
	const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;                        // RFC2295
	const HTTP_INSUFFICIENT_STORAGE = 507;                                        // RFC4918
	const HTTP_LOOP_DETECTED = 508;                                               // RFC5842
	const HTTP_NOT_EXTENDED = 510;                                                // RFC2774
	const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;                             // RFC6585

	/**
	 * @var int
	 */
	protected $statusCode = 200;

	/**
	 * Status codes translation table.
	 *
	 * @NOTE HTTP status texts from Symfony\Component\HttpFoundation\Response
	 *
	 * The list of codes is complete according to the
	 * {@link http://www.iana.org/assignments/http-status-codes/ Hypertext Transfer Protocol (HTTP) Status Code Registry}
	 * (last updated 2012-02-13).
	 *
	 * Unless otherwise noted, the status code is defined in RFC2616.
	 *
	 * @var array
	 */
	public static $statusTexts = [
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',            // RFC2518
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',          // RFC4918
		208 => 'Already Reported',      // RFC5842
		226 => 'IM Used',               // RFC3229
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => 'Reserved',
		307 => 'Temporary Redirect',
		308 => 'Permanent Redirect',    // RFC7238
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',                                               // RFC2324
		422 => 'Unprocessable Entity',                                        // RFC4918
		423 => 'Locked',                                                      // RFC4918
		424 => 'Failed Dependency',                                           // RFC4918
		425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
		426 => 'Upgrade Required',                                            // RFC2817
		428 => 'Precondition Required',                                       // RFC6585
		429 => 'Too Many Requests',                                           // RFC6585
		431 => 'Request Header Fields Too Large',                             // RFC6585
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
		507 => 'Insufficient Storage',                                        // RFC4918
		508 => 'Loop Detected',                                               // RFC5842
		510 => 'Not Extended',                                                // RFC2774
		511 => 'Network Authentication Required',                             // RFC6585
	];

	/**
	 * @var string
	 */
	protected $contentType = 'text/html';

	/**
	 * @var string
	 */
	protected $charset = 'UTF-8';

	/**
	 * @var array
	 */
	protected $headers = [];

	/**
	 * @var string
	 */
	protected $body = '';

	/**
	 * @var \Rise\Http\Request
	 */
	protected $request;

	/**
	 * @var \Rise\Template
	 */
	protected $template;

	/**
	 * @var \Rise\Router\UrlGenerator
	 */
	protected $urlGenerator;

	public function __construct(
		Request $request,
		Template $template,
		UrlGenerator $urlGenerator
	) {
		$this->request = $request;
		$this->template = $template;
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * Setup HTTP response for a HTML page.
	 *
	 * @param string $template
	 * @param array $data optional
	 * @return self
	 */
	public function html($template = '', $data = []) {
		$body = $this->template->render($template, $data);
		$this->setBody($body);
		return $this;
	}

	/**
	 * Setup HTTP response for JSON.
	 *
	 * @param array $data
	 * @return self
	 */
	public function json($data = []) {
		$this->setHeader('Content-Type', 'application/json')
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
		$this->setStatusCode($statusCode)
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
		$this->redirect($this->urlGenerator->generate($name, $params));
		return $this;
	}

	/**
	 * @return self
	 */
	public function send() {
		if ($this->request && $this->request->isMethod('HEAD')) {
			$this->unsetHeader('Content-Length');
			$this->setBody('');
		}

		if (!$this->hasHeader('Content-Type')) {
			$this->setHeader('Content-Type', $this->contentType.'; charset='.$this->charset);
		}

		$this->sendHeaders();
		$this->sendBody();

		// echo memory_get_usage();
		// echo ' ';
		// echo memory_get_peak_usage(true);

		if (function_exists('fastcgi_finish_request')) {
			fastcgi_finish_request();
		} elseif ('cli' !== PHP_SAPI) {
			static::closeOutputBuffers(0, true);
		}

		return $this;
	}

	/**
	 * Set HTTP status code.
	 *
	 * @param int $code
	 * @return self
	 */
	public function setStatusCode($code = 200) {
		$this->statusCode = (int)$code;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * @param string $name
	 * @param string|string[] $value
	 * @return self
	 */
	public function setHeader($name, $value) {
		if (is_array($value)) {
			$this->headers[$name] = $value;
		} else {
			$this->headers[$name] = [$value];
		}
		return $this;
	}

	/**
	 * @param string $name
	 * @return self
	 */
	public function unsetHeader($name = '') {
		if ($this->hasHeader($name)) {
			unset($this->headers[$name]);
		}
		return $this;
	}

	/**
	 * @param string $name
	 * @param string|string[] $value
	 * @return self
	 */
	public function addHeader($name, $value) {
		if ($this->hasHeader($name)) {
			if (is_array($value)) {
				array_push($this->headers[$name], $value);
			} else {
				$this->headers[$name][] = $value;
			}
		} else {
			$this->setHeader($name, $value);
		}
		return $this;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasHeader($name = '') {
		return array_key_exists($name, $this->headers);
	}

	/**
	 * @param string $body
	 * @return self
	 */
	public function setBody($body = '') {
		$this->body = $body;
		return $this;
	}

	/**
	 * @param string $contentType
	 * @return self
	 */
	public function setContentType($contentType = '') {
		$this->contentType = $contentType;
		return $this;
	}

	/**
	 * @return self
	 */
	public function setCharset($charset = '') {
		$this->charset = $charset;
		return $this;
	}

	/**
	 * @return self
	 */
	protected function sendHeaders() {
		header($this->getStatusLine(), true, $this->statusCode);

		$headers = $this->getHeaders();
		foreach ($headers as $name => $values) {
			foreach ($values as $value) {
				header($name.': '.$value, false, $this->statusCode);
			}
		}

		// @TODO set cookies
		// setcookie();

		header_remove('X-Powered-By');

		return $this;
	}

	/**
	 * @return string
	 */
	protected function getStatusLine() {
		$serverProtocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : null;
		if ($serverProtocol == 'HTTP/1.1' || $serverProtocol == 'HTTP/1.0') {
		} else {
			$serverProtocol = 'HTTP/1.1';
		}
		$statusText = isset($this->statusTexts[$this->statusCode]) ? $this->statusTexts[$this->statusCode] : '';
		$statusLine = $serverProtocol.' '.$this->statusCode.' '.$statusText;
		return $statusLine;
	}

	/**
	 * @return self
	 */
	protected function sendBody() {
		echo $this->body;
		return $this;
	}

	/**
	 * Cleans or flushes output buffers up to target level.
	 *
	 * @NOTE Function from Symfony\Component\HttpFoundation\Response
	 *
	 * Resulting level can be greater than target level if a non-removable buffer has been encountered.
	 *
	 * @param int  $targetLevel The target output buffering level
	 * @param bool $flush       Whether to flush or clean the buffers
	 */
	public static function closeOutputBuffers($targetLevel, $flush) {
		$status = ob_get_status(true);
		$level = count($status);

		while ($level-- > $targetLevel
			   && (!empty($status[$level]['del'])
				   || (isset($status[$level]['flags'])
					   && ($status[$level]['flags'] & PHP_OUTPUT_HANDLER_REMOVABLE)
					   && ($status[$level]['flags'] & ($flush ? PHP_OUTPUT_HANDLER_FLUSHABLE : PHP_OUTPUT_HANDLER_CLEANABLE))
				   )
			   )
		) {
			if ($flush) {
				ob_end_flush();
			} else {
				ob_end_clean();
			}
		}
	}
}
