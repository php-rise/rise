<?php
namespace Rise\Test\Container;

use PHPUnit\Framework\TestCase;
use Rise\Container;
use Rise\Container\DynamicFactory;
use Rise\Test\Container\DynamicFactoryTest\Example;

final class DynamicFactoryTest extends TestCase {
	public function testFactoryCreate() {
		$container = new Container();
		$factory = new DynamicFactory($container);
		$example = $factory->create(Example::class);
		$this->assertInstanceOf(Example::class, $example);
	}
}
