<?php
namespace Rise\Request;

use Rise\Request\Upload\FileFactory;

class Upload {
	/**
	 * $_FILES data with rearranged order of the first two levels of array keys.
	 *
	 * @var array|null
	 */
	protected $filesData = null;

	/**
	 * File instances.
	 *
	 * @var array
	 */
	protected $files = [];

	/**
	 * @var \Rise\Request\Upload\FileFactory
	 */
	protected $fileFactory;

	public function __construct(FileFactory $fileFactory) {
		$this->fileFactory = $fileFactory;
	}

	/**
	 * Get uploaded file or files.
	 *
	 * @param string $key
	 * @return \Rise\Request\Upload\File|\Rise\Request\Upload\File[]|null
	 */
	public function getFile($key) {
		if (isset($this->files[$key])) {
			return $this->files[$key];
		}

		if (!isset($_FILES[$key])
			|| !isset($_FILES[$key]['error'])
		) {
			return null;
		}

		$field = $_FILES[$key];

		// Check if it is multiple files upload
		if (is_array($field['error'])) {
			$files = [];

			foreach ($field['error'] as $subkey => $value) {
				$files[] = $this->createFile(
					$field['name'][$subkey],
					$field['type'][$subkey],
					$field['tmp_name'][$subkey],
					$field['error'][$subkey],
					$field['size'][$subkey]
				);
			}

			$this->files[$key] = $files;
		} else {
			$this->files[$key] = $this->createFile(
				$field['name'],
				$field['type'],
				$field['tmp_name'],
				$field['error'],
				$field['size']
			);
		}

		return $this->files[$key];
	}

	/**
	 * @param string $name
	 * @param string $type
	 * @param string $tmpName
	 * @param int $error
	 * @param int $size
	 * @return \Rise\Request\Upload\File
	 */
	protected function createFile($name, $type, $tmpName, $error, $size) {
		$file = $this->fileFactory->create();
		$file->setName($name);
		$file->setType($type);
		$file->setTmpName($tmpName);
		$file->setError($error);
		$file->setSize($size);
		return $file;
	}
}
