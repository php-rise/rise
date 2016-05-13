<?php
namespace Rise\Services\Http;

use Rise\Services\BaseService;

use Rise\Components\Http\Upload\File;

class Upload extends BaseService {
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
	 * Get uploaded file or files.
	 *
	 * @param string $key
	 * @return \Rise\Components\Http\Request\Upload\File|array|null
	 */
	public function getFile($key = '') {
		if (isset($this->files[$key])) {
			return $this->file[$key];
		}

		if (!isset($_FILES[$key])
			|| !isset($_FILES[$key]['error'])
		) {
			return null;
		}

		if ($this->filesData === null) {
			$this->generateFilesData();
		}

		$this->files[$key] = $this->generateFileInstances($this->filesData['error'][$key], [$key]);
		return $this->files[$key];
	}

	/**
	 * Swap the first two levels array keys to make the data consistent for recursive processing.
	 *
	 * @return self
	 */
	protected function generateFilesData() {
		foreach ($_FILES as $level1Key => $level1Value) {
			foreach ($level1Value as $level2Key => $level2Value) {
				if (!isset($this->filesData[$level2Key])) {
					$this->filesData[$level2Key] = [];
				}
				$this->filesData[$level2Key][$level1Key] = $level2Value;
			}
		}
		return $this;
	}

	/**
	 * Generate file instances and set them with appropriate uploaded file information.
	 *
	 * @param array $data
	 * @param string $filesDataKeys
	 * @return \Rise\Components\Http\Request\Upload\File|array
	 */
	protected function generateFileInstances($data, $filesDataKeys) {
		if (is_array($data)) {
			$array = [];
			foreach ($data as $key => $value) {
				$filesDataKeys[] = $key;
				$array[$key] = $this->generateFileInstances($value, $filesDataKeys);
			}
			return $array;
		}

		$informationKeys = ['name', 'type', 'size', 'tmp_name', 'error'];
		$file = new File;

		foreach ($informationKeys as $informationKey) {
			$filesDataReference = &$this->filesData[$informationKey];
			foreach ($filesDataKeys as $filesDataKey) {
				$filesDataReference = &$filesDataReference[$filesDataKey];
			}
			switch ($informationKey) {
			case 'name':
				$file->setName($filesDataReference);
				break;
			case 'type':
				$file->setType($filesDataReference);
				break;
			case 'size':
				$file->setSize($filesDataReference);
				break;
			case 'tmp_name':
				$file->setTmpName($filesDataReference);
				break;
			case 'error':
				$file->setError($filesDataReference);
				break;
			}
		}

		return $file;
	}
}
