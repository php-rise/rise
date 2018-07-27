<?php
namespace Rise\Request\Upload;

use Rise\Container\BaseFactory;

class FileFactory extends BaseFactory {
	public function create() {
		return $this->container->getNewInstance('Rise\Request\Upload\File');
	}
}
