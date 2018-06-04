<?php
namespace Rise\Http\Upload;

use Rise\Container\BaseFactory;

class FileFactory extends BaseFactory {
	public function create() {
		return $this->container->get('Rise\Http\Upload\File');
	}
}
