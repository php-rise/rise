<?php
namespace Rise\Test\Http\Request;

use PHPUnit\Framework\TestCase;
use Rise\Http\Request\Upload;
use Rise\Http\Request\Upload\File;
use Rise\Http\Request\Upload\FileFactory;

final class UploadTest extends TestCase {
	public function setUp() {
		$_FILES['file'] = [
			'name' => 'photo.png',
			'type' => 'image/png',
			'tmp_name' => '/tmp/php123123',
			'error' => 0,
			'size' => 1000,
		];

		$_FILES['files'] = [
			'name' => [
				'photo1.png',
				'photo2.png',
				'photo3.png',
			],
			'type' => [
				'image/png',
				'image/png',
				'image/png',
			],
			'tmp_name' => [
				'/tmp/php123123',
				'/tmp/php456456',
				'/tmp/php789789',
			],
			'error' => [
				0,
				0,
				0,
			],
			'size' => [
				1000,
				2000,
				30000,
			],
		];
	}

	public function tearDown() {
		unset($_FILES['file']);
		unset($_FILES['files']);
	}

	public function testNotExistsField() {
		$factory = $this->createMock(FileFactory::class);

		$upload = new Upload($factory);

		$this->assertNull($upload->getFile('notExists'));
	}

	public function testGetSingleFile() {
		$factory = $this->createMock(FileFactory::class);

		$factory->expects($this->once())
			->method('create')
			->will($this->returnCallback(function () {
				return $this->createMock(File::class);
			}));

		$upload = new Upload($factory);

		$file = $upload->getFile('file');
		$fileClone = $upload->getFile('file');

		$this->assertSame($file, $fileClone);
		$this->assertInstanceOf(File::class, $file);
	}

	public function testGetMultipleFiles() {
		$factory = $this->createMock(FileFactory::class);

		$factory->expects($this->exactly(3))
			->method('create')
			->will($this->returnCallback(function () {
				return $this->createMock(File::class);
			}));

		$upload = new Upload($factory);

		$files = $upload->getFile('files');
		$filesClone = $upload->getFile('files');

		$this->assertSame($files, $filesClone);
		$this->assertTrue(is_array($files));
		$this->assertCount(3, $files);
		foreach ($files as $file) {
			$this->assertInstanceOf(File::class, $file);
		}
	}
}
