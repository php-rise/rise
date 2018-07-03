<?php
namespace Rise\Http\Request\Upload;

use Rise\Container\BaseFactory;

class FileFactory extends BaseFactory {
	public function create() {
		return $this->container->getNewInstance('Rise\Http\Request\Upload\File');
	}
}
