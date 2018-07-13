<?php
namespace Rise\Test\Request;

use PHPUnit\Framework\TestCase;
use Rise\Request\Upload;
use Rise\Request\Upload\File;
use Rise\Request\Upload\FileFactory;

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

		$_FILES['form'] = [
			'name' => [
				'user' => [
					'photo' => 'photo.png',
				]
			],
			'type' => [
				'user' => [
					'photo' => 'image/png',
				]
			],
			'tmp_name' => [
				'user' => [
					'photo' => '/tmp/php123123',
				]
			],
			'error' => [
				'user' => [
					'photo' => 0,
				]
			],
			'size' => [
				'user' => [
					'photo' => 1000,
				]
			],
		];
	}

	public function tearDown() {
		unset($_FILES['file']);
		unset($_FILES['files']);
		unset($_FILES['form']);
	}

	public function testGetFiles() {
		$factory = $this->createMock(FileFactory::class);

		$factory->expects($this->exactly(5))
			->method('create')
			->will($this->returnCallback(function () {
				return $this->createMock(File::class);
			}));

		$upload = new Upload($factory);
		$files = $upload->getFiles();

		$this->assertInstanceOf(File::class, $files['file']);
		$this->assertInstanceOf(File::class, $files['files'][0]);
		$this->assertInstanceOf(File::class, $files['files'][1]);
		$this->assertInstanceOf(File::class, $files['files'][2]);
		$this->assertCount(3, $files['files']);
		$this->assertInstanceOf(File::class, $files['form']['user']['photo']);
	}
}
