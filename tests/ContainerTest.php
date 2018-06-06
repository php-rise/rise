<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use Rise\Container;
use Rise\Container\NotFoundException;
use Rise\Test\ContainerTest\Singleton;
use Rise\Test\ContainerTest\Factory;
use Rise\Test\ContainerTest\DependencyA;
use Rise\Test\ContainerTest\DependencyB;
use Rise\Test\ContainerTest\AutoWired;
use Rise\Test\ContainerTest\MissingDependency;
use Rise\Test\ContainerTest\BaseBinding;
use Rise\Test\ContainerTest\AliasBinding;

final class ContainerTest extends TestCase {
	public function testSingleton() {
		$container = new Container();

		$singleton1 = $container->get(Singleton::class);
		$singleton2 = $container->get(Singleton::class);

		$this->assertSame($singleton1, $singleton2);

		$container->bindFactory(Factory::class);
		$factory1 = $container->get(Factory::class);
		$factory2 = $container->get(Factory::class);

		$this->assertSame($factory1, $factory2);
	}

	public function testContainerInjectionInFactory() {
		$container = new Container();
		$container->bindFactory(Factory::class);
		$factory = $container->get(Factory::class);
		$this->assertSame($container, $factory->getContainer());
	}

	public function testAutoWiring() {
		$container = new Container();
		$autoWired = $container->get(AutoWired::class);
		$this->assertInstanceOf(DependencyA::class, $autoWired->a);
		$this->assertInstanceOf(DependencyB::class, $autoWired->b);
	}

	public function testClassNotFound() {
		$this->expectException(NotFoundException::class);
		$container = new Container();
		$container->get('App\God');
	}

	public function testParameterNotFound() {
		$this->expectException(NotFoundException::class);
		$container = new Container();
		$container->get(MissingDependency::class);
	}

	public function testAlias() {
		$container = new Container();
		$container->bind(BaseBinding::class, AliasBinding::class);
		$aliasBinding = $container->get(BaseBinding::class);
		$this->assertInstanceOf(AliasBinding::class, $aliasBinding);
	}
}
