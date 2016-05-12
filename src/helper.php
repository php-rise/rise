<?php

/**
 * Get a service or the service locator.
 *
 * @param string $name Optional.
 * @return \Rise\ServiceLocator|\Rise\Services\BaseService
 */
function service($name = null) {
	if ($name === null) {
		return \Rise\ServiceLocator::getInstance();
	}
	return \Rise\ServiceLocator::getInstance()->getService($name);
}
