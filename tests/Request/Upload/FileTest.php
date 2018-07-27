<?php
namespace Rise\Test\Request\Upload;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Rise\Request\Upload\File;

final class FileTest extends TestCase {
	private $root;

	public function setUp() {
		$this->root = vfsStream::setup('root', null, [
			'tmp' => [
				'php123123' => 'Upload content',
			],
			'storage' => [
				'existing_file.txt' => 'Existing content',
			],
		]);
	}

	public function testMoveFile() {
		$file = new File();
		$file->setName('some_file.txt');
		$file->setType('text/plain');
		$file->setTmpName(vfsStream::url('root/tmp/php123123'));
		$file->setError(0);
		$file->setSize(12);

		$this->assertSame(
			vfsStream::url('root/storage/some_file.txt'),
			$file->moveTo(vfsStream::url('root/storage/some_file.txt'))
		);
		$this->assertTrue($this->root->getChild('storage')->hasChild('some_file.txt'));
		$this->assertSame(
			'Upload content',
			$this->root->getChild('storage')->getChild('some_file.txt')->getContent()
		);
	}

	public function testNameCollisionWhenMoveFile() {
		$file = new File();
		$file->setName('some_file.txt');
		$file->setType('text/plain');
		$file->setTmpName(vfsStream::url('root/tmp/php123123'));
		$file->setError(0);
		$file->setSize(12);

		$this->assertSame(
			vfsStream::url('root/storage/existing_file-1.txt'),
			$file->moveTo(vfsStream::url('root/storage/existing_file.txt'))
		);
		$this->assertTrue($this->root->getChild('storage')->hasChild('existing_file-1.txt'));
		$this->assertSame(
			'Upload content',
			$this->root->getChild('storage')->getChild('existing_file-1.txt')->getContent()
		);
	}

	public function testMoveFileToDirectory() {
		$file = new File();
		$file->setName('some_file.txt');
		$file->setType('text/plain');
		$file->setTmpName(vfsStream::url('root/tmp/php123123'));
		$file->setError(0);
		$file->setSize(12);

		$this->assertSame(
			vfsStream::url('root/storage/some_file.txt'),
			$file->moveToDirectory(vfsStream::url('root/storage'))
		);
		$this->assertTrue($this->root->getChild('storage')->hasChild('some_file.txt'));
		$this->assertSame(
			'Upload content',
			$this->root->getChild('storage')->getChild('some_file.txt')->getContent()
		);
	}

	public function testValidity() {
		$file = new File();
		$file->setName('some_file.txt');
		$file->setType('text/plain');
		$file->setTmpName(vfsStream::url('root/tmp/php123123'));
		$file->setError(0);
		$file->setSize(12);

		$this->assertTrue($file->isValid());

		$file->setError(1);

		$this->assertFalse($file->isValid());

		$file->setError(0);
		$file->setSize(100000001);

		$this->assertFalse($file->isValid());

		$file->setMaxFileSize(-1);

		$this->assertTrue($file->isValid());
	}
}
