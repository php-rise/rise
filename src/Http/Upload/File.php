<?php
namespace Rise\Http\Upload;

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
	 * Size in bytes.
	 * $_FILES['<key>']['error']
	 *
	 * @var int
	 */
	protected $size;

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
	 * Reference: http://php.net/manual/en/features.file-upload.errors.php
	 *
	 * @var int
	 */
	protected $error;

	/**
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
	 * @param int $size
	 * @return self
	 */
	public function setSize($size) {
		$this->size = $size;
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

		if ($this->size > $this->maxFileSize) {
			return false;
		}

		return true;
	}

	/**
	 * @param string $directory
	 * @param string $prefix Optional, prefix that is not included in the returned path
	 * @return string|null Path of the moved file.
	 */
	public function moveToDirectory($directory, $prefix = '') {
		if (!$this->isValid()) {
			return null;
		}

		$fileFullName = $this->name;
		$path = $directory . '/' . $fileFullName;
		if (file_exists($path)) {
			$filename = pathinfo($fileFullName, PATHINFO_FILENAME);
			$extension = pathinfo($fileFullName, PATHINFO_EXTENSION);
			$count = 0;
			do {
				$fileFullName = $filename . '-' . ++$count . '.' . $extension;
				$path = $directory . '/' . $fileFullName;
			} while (file_exists($path));
		}

		$moved = move_uploaded_file($this->tmpName, $path);
		if (!$moved) {
			return null;
		}

		if (substr($directory, 0, strlen($prefix)) === $prefix) {
			$directory = substr($directory, strlen($prefix));
		}

		return $directory . '/' . $fileFullName;
	}
}
