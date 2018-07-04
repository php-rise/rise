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
				'php456456' => 'Upload content 2',
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
			$file->moveToDirectory(vfsStream::url('root/storage'))
		);
		$this->assertTrue($this->root->getChild('storage')->hasChild('some_file.txt'));
		$this->assertSame(
			'Upload content',
			$this->root->getChild('storage')->getChild('some_file.txt')->getContent()
		);
	}

	public function testNameCollisionWhenMoveFile() {
		$file1 = new File();
		$file1->setName('existing_file.txt');
		$file1->setType('text/plain');
		$file1->setTmpName(vfsStream::url('root/tmp/php123123'));
		$file1->setError(0);
		$file1->setSize(12);

		$file2 = new File();
		$file2->setName('existing_file.txt');
		$file2->setType('text/plain');
		$file2->setTmpName(vfsStream::url('root/tmp/php456456'));
		$file2->setError(0);
		$file2->setSize(12);

		// Move first file
		$this->assertSame(
			vfsStream::url('root/storage/existing_file-1.txt'),
			$file1->moveToDirectory(vfsStream::url('root/storage'))
		);
		$this->assertTrue($this->root->getChild('storage')->hasChild('existing_file-1.txt'));
		$this->assertSame(
			'Upload content',
			$this->root->getChild('storage')->getChild('existing_file-1.txt')->getContent()
		);

		// Move second file
		$this->assertSame(
			vfsStream::url('root/storage/existing_file-2.txt'),
			$file2->moveToDirectory(vfsStream::url('root/storage'))
		);
		$this->assertTrue($this->root->getChild('storage')->hasChild('existing_file-2.txt'));
		$this->assertSame(
			'Upload content 2',
			$this->root->getChild('storage')->getChild('existing_file-2.txt')->getContent()
		);
	}

	public function testMoveFileTrimPrefix() {
		$file = new File();
		$file->setName('some_file.txt');
		$file->setType('text/plain');
		$file->setTmpName(vfsStream::url('root/tmp/php123123'));
		$file->setError(0);
		$file->setSize(12);

		$this->assertSame(
			'storage/some_file.txt',
			$file->moveToDirectory(vfsStream::url('root/storage'), vfsStream::url('root/'))
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
