<?php
namespace Rise\Router;

class UrlGenerator {
	const ROUTE_PARAM_PATTERN = '/(\\{(.*?)\\})/';

	/**
	 * Paths map for URL generation.
	 * Format: [
	 *             '<route name 1>' => '<path>',
	 *             '<route name 2>' => [
	 *                 'params' => [
	 *                     '<param name>' => '<index>',
	 *                 ],
	 *                 'chunks' => [],
	 *             ],
	 *         ]
	 * @var array
	 */
	protected $compiled = [];

	/**
	 * A route name to route path map.
	 * Format: [
	 *             '<route name>' => '<route path>',
	 *         ]
	 * @var array
	 */
	protected $raw = [];

	/**
	 * @param string $name
	 * @param string $routePath
	 */
	public function add($name, $routePath) {
		$this->raw[$name] = $routePath;
	}

	/**
	 * Generate URL.
	 *
	 * @param string $name
	 * @param array $params
	 * @return string
	 */
	public function generate($name, $params = []) {
		if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
			&& $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'
		) {
			$scheme = 'https';
		} else {
			$scheme = 'http';
		}
		return $scheme . '://' . $_SERVER['HTTP_HOST'] . $this->generatePath($name, $params);
	}

	/**
	 * Compile raw route to a format for generation.
	 *
	 * @param string $name
	 */
	protected function compile($name) {
		if (!isset($this->raw[$name])) {
			return;
		}

		$count = preg_match_all(
			self::ROUTE_PARAM_PATTERN,
			$this->raw[$name],
			$matches,
			PREG_OFFSET_CAPTURE
		);

		if ($count === 0) {
			$this->compiled[$name] = $this->raw[$name];
		} else if ($count > 0) {
			$routePath = $this->raw[$name];
			$params = [];
			$chunks = [];
			$pos = 0;
			for ($i = 0; $i < $count; $i++) {
				array_push($chunks, substr($routePath, $pos, $matches[1][$i][1] - $pos));
				$size = array_push($chunks, ''); // Empty string is just a placeholder, it can be anything
				$params[$matches[2][$i][0]] = $size - 1; // Set index
				$pos = $matches[1][$i][1] + strlen($matches[1][$i][0]);
			}
			array_push($chunks, substr($routePath, $pos));
			$this->compiled[$name] = [
				'params' => $params,
				'chunks' => $chunks,
			];
		}
	}

	/**
	 * Get a compiled route.
	 *
	 * @param string $name
	 * @return string|array|null
	 */
	protected function getCompiledRoute($name) {
		if (!isset($this->compiled[$name])) {
			$this->compile($name);
		}

		return isset($this->compiled[$name]) ? $this->compiled[$name] : null;
	}

	/**
	 * Generate path from compiled route.
	 *
	 * @param string $name
	 * @param array $params
	 * @return string
	 */
	protected function generatePath($name, $params = []) {
		$result = '';
		$compiledRoute = $this->getCompiledRoute($name);

		if (is_string($compiledRoute)) {
			$result = $compiledRoute;
		} else if (is_array($compiledRoute)) {
			$chunks = $compiledRoute['chunks'];
			foreach ($params as $paramName => $value) {
				if (isset($compiledRoute['params'][$paramName])) {
					$index = $compiledRoute['params'][$paramName];
					$chunks[$index] = $value;
				}
			}
			$result = implode($chunks);
		}

		return $result;
	}
}
