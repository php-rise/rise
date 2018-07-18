<?php
namespace Rise\Request\Upload;

class File {
	/**
	 * Original filename on client machine.
	 * $_FILES['<key>']['name']
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * MIME type.
	 * $_FILES['<key>']['type']
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Temporary filename on server.
	 * $_FILES['<key>']['tmp_name']
	 *
	 * @var string
	 */
	protected $tmpName;

	/**
	 * Error code.
	 * $_FILES['<key>']['error']
	 * See http://php.net/manual/en/features.file-upload.errors.php
	 *
	 * @var int
	 */
	protected $error;

	/**
	 * Size in bytes.
	 * $_FILES['<key>']['size']
	 *
	 * @var int
	 */
	protected $size;

	/**
	 * Maximum allowed file size. -1 is unlimited.
	 *
	 * @var int
	 */
	protected $maxFileSize = 100000000;

	/**
	 * @param string $name
	 * @return self
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * @param string $type
	 * @return self
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	/**
	 * @param string $tmpName
	 * @return self
	 */
	public function setTmpName($tmpName) {
		$this->tmpName = $tmpName;
		return $this;
	}

	/**
	 * @param int $error
	 * @return self
	 */
	public function setError($error) {
		$this->error = $error;
		return $this;
	}

	/**
	 * @param int $size
	 * @return self
	 */
	public function setSize($size) {
		$this->size = $size;
		return $this;
	}

	/**
	 * @param int $size
	 * @return self
	 */
	public function setMaxFileSize($size) {
		$this->maxFileSize = $size;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isValid() {
		if ($this->error === null
			|| $this->error != UPLOAD_ERR_OK
		) {
			return false;
		}

		if ($this->maxFileSize >= 0
			&& $this->size > $this->maxFileSize
		) {
			return false;
		}

		return true;
	}

	/**
	 * Move file.
	 *
	 * @param string $destination
	 * @return string|null Path of the moved file.
	 */
	public function moveTo($destination) {
		if (!$this->isValid()) {
			return null;
		}

		$path = $this->generateFinalPath($destination);
		$moved = move_uploaded_file($this->tmpName, $path);

		if (!$moved) {
			return null;
		}

		return $path;
	}

	/**
	 * Move file to a directory.
	 *
	 * @param string $directory
	 * @return string|null Path of the moved file.
	 */
	public function moveToDirectory($directory) {
		return $this->moveTo($directory . '/' . $this->name);
	}

	/**
	 * @param string $filename
	 * @return string
	 */
	protected function generateFinalPath($filePath) {
		if (!file_exists($filePath)) {
			return $filePath;
		}

		$directory = dirname($filePath);
		$fileFullName = basename($filePath);
		$filename = pathinfo($fileFullName, PATHINFO_FILENAME) . '-';
		$extension = pathinfo($fileFullName, PATHINFO_EXTENSION);

		if ($extension !== '') {
			$extension = '.' . $extension;
		}

		$count = 0;
		do {
			$finalPath = $directory . '/' . $filename . ++$count . $extension;
		} while (file_exists($finalPath));

		return $finalPath;
	}
}
