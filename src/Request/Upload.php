<?php
namespace Rise\Request;

use Rise\Request\Upload\FileFactory;

class Upload {
	/**
	 * File instances.
	 *
	 * @var array|null
	 */
	protected $files = null;

	/**
	 * @var \Rise\Request\Upload\FileFactory
	 */
	private $fileFactory;

	public function __construct(FileFactory $fileFactory) {
		$this->fileFactory = $fileFactory;
	}

	/**
	 * Get uploaded files.
	 *
	 * @param string $key
	 * @return array
	 */
	public function getFiles() {
		if (is_null($this->files)) {
			$this->generateFiles();
		}

		return $this->files;
	}

	protected function generateFiles() {
		if (empty($_FILES)) {
			$this->files = [];
			return;
		}

		$files = [];

		foreach ($_FILES as $key => $field) {
			if (is_int($field['error'])) {
				$files[$key] = $this->createFile(
					isset($field['name']) ? $field['name'] : null,
					isset($field['type']) ? $field['type'] : null,
					$field['tmp_name'],
					$field['error'],
					$field['size']
				);
			} else if (is_array($field['error'])) {
				$files[$key] = $this->normalize(
					isset($field['name']) ? $field['name'] : null,
					isset($field['type']) ? $field['type'] : null,
					$field['tmp_name'],
					$field['error'],
					$field['size']
				);
			}
		}

		$this->files = $files;
	}

	/**
	 * @param string $name
	 * @param string $type
	 * @param string $tmpName
	 * @param int $error
	 * @param int $size
	 * @return \Rise\Request\Upload\File
	 */
	private function createFile($name, $type, $tmpName, $error, $size) {
		$file = $this->fileFactory->create();
		$file->setName($name);
		$file->setType($type);
		$file->setTmpName($tmpName);
		$file->setError($error);
		$file->setSize($size);
		return $file;
	}

	/**
	 * @param array $nameTree
	 * @param array $typeTree
	 * @param array $tmpNameTree
	 * @param array $errorTree
	 * @param array $sizeTree
	 * @return array
	 */
	private function normalize($nameTree, $typeTree, $tmpNameTree, $errorTree, $sizeTree) {
		$files = [];

		foreach ($errorTree as $key => $field) {
			if (is_array($field)) {
				$files[$key] = $this->normalize(
					isset($nameTree[$key]) ? $nameTree[$key] : null,
					isset($typeTree[$key]) ? $typeTree[$key] : null,
					$tmpNameTree[$key],
					$field,
					$sizeTree[$key]
				);
			} else {
				$files[$key] = $this->createFile(
					isset($nameTree[$key]) ? $nameTree[$key] : null,
					isset($typeTree[$key]) ? $typeTree[$key] : null,
					$tmpNameTree[$key],
					$field,
					$sizeTree[$key]
				);
			}
		}

		return $files;
	}
}
