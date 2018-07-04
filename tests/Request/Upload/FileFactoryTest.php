<?php
namespace Rise\Test\Request\Upload;

use PHPUnit\Framework\TestCase;
use Rise\Request\Upload\FileFactory;
use Rise\Request\Upload\File;
use Rise\Container;

final class FileFactoryTest extends TestCase {
	public function testCreate() {
		$container = $this->createMock(Container::class);

		$container->expects($this->never())
			->method('get');

		$container->expects($this->any())
			->method('getNewInstance')
			->with($this->equalTo(File::class))
			->will($this->returnCallback(function () {
				return $this->createMock(File::class);
			}));

		$factory = new FileFactory($container);

		$this->assertInstanceOf(File::class, $factory->create());
	}
}
