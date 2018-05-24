<?php
namespace Rise\Factories\Http\Upload;

use Rise\Factories\BaseFactory;

class FileFactory extends BaseFactory {
	public function create() {
		return $this->container->get('Rise\Components\Http\Upload\File');
	}
}
