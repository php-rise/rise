<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use Rise\Container;
use Rise\Container\NotFoundException;
use Rise\Container\NotInstantiableException;
use Rise\Container\NotAllowedException;
use Rise\Container\CyclicDependencyException;
use Rise\Container\InvalidRuleException;
use Rise\Test\ContainerTest\Singleton;
use Rise\Test\ContainerTest\AutoWired;
use Rise\Test\ContainerTest\DependencyA;
use Rise\Test\ContainerTest\DependencyB;
use Rise\Test\ContainerTest\DependencyC;
use Rise\Test\ContainerTest\BaseBinding;
use Rise\Test\ContainerTest\AliasBinding;
use Rise\Test\ContainerTest\Factory;
use Rise\Test\ContainerTest\NotClassInterface;
use Rise\Test\ContainerTest\MissingDependency;
use Rise\Test\ContainerTest\PrimitiveTypeParam;
use Rise\Test\ContainerTest\Cyclic;
use Rise\Test\ContainerTest\CyclicA;
use Rise\Test\ContainerTest\CyclicB;
use Rise\Test\ContainerTest\ConfigConstructor;
use Rise\Test\ContainerTest\ConfigMethod;
use Rise\Test\ContainerTest\MethodInjectionWithConstructor;
use Rise\Test\ContainerTest\MethodInjectionWithoutConstructor;
use Rise\Test\ContainerTest\MethodInjectionWithExtraMappings;

final class ContainerTest extends TestCase {
	public function testShouldGetTheSameInstance() {
		$container = new Container();

		$singleton1 = $container->get(Singleton::class);
		$singleton2 = $container->get(Singleton::class);

		$this->assertSame($singleton1, $singleton2);
	}

	public function testAutoWiring() {
		$container = new Container();

		$autoWired = $container->get(AutoWired::class);

		$this->assertInstanceOf(DependencyA::class, $autoWired->a);
		$this->assertInstanceOf(DependencyB::class, $autoWired->b);
	}

	public function testBind() {
		$container = new Container();

		$container->bind(BaseBinding::class, AliasBinding::class);
		$aliasBinding = $container->get(BaseBinding::class);

		$this->assertInstanceOf(AliasBinding::class, $aliasBinding);
	}

	public function testBindFactory() {
		$container = new Container();

		$container->bindFactory(Factory::class);
		$factory = $container->get(Factory::class);

		$this->assertSame($container, $factory->getContainer());

		$factoryAgain = $container->get(Factory::class);

		$this->assertSame($factory, $factoryAgain);
	}

	public function testBindSingleton() {
		$container = new Container();
		$singleton = new Singleton();

		$container->bindSingleton(Singleton::class, $singleton);
		$instance = $container->get(Singleton::class);

		$this->assertSame($singleton, $instance);
	}

	public function testHas() {
		$container = new Container();
		$singleton = new Singleton();

		$container->bindSingleton(Singleton::class, $singleton);

		$this->assertTrue($container->has(Singleton::class));
		$this->assertTrue($container->has(AutoWired::class));
		$this->assertFalse($container->has(BaseBinding::class));

		$container->bind(BaseBinding::class, AliasBinding::class);

		$this->assertTrue($container->has(BaseBinding::class));
	}

	public function testNotClass() {
		$this->expectException(NotInstantiableException::class);
		$container = new Container();
		$container->get(NotClassInterface::class);
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

	public function testNotAllowPrmitiveTypes() {
		$this->expectException(NotAllowedException::class);
		$container = new Container();
		$container->get(PrimitiveTypeParam::class);
	}

	public function testCyclicDependencyFromStart() {
		$this->expectException(CyclicDependencyException::class);
		$container = new Container();
		$container->get(Cyclic::class);
	}

	public function testCyclicDependencyInTheMiddle() {
		$this->expectException(CyclicDependencyException::class);
		$container = new Container();
		$container->get(CyclicA::class);
	}

	public function testConfigConstructor() {
		$container = new Container();

		$rules = [BaseBinding::class => AliasBinding::class];
		$container->configClass(ConfigConstructor::class, $rules);
		$instance = $container->get(ConfigConstructor::class);

		$this->assertInstanceOf(AliasBinding::class, $instance->getDep());
	}

	public function testConfigConstructorWithInvalidRule() {
		$this->expectException(InvalidRuleException::class);

		$container = new Container();

		$rules = [BaseBinding::class => 1];
		$container->configClass(ConfigConstructor::class, $rules);
		$container->get(ConfigConstructor::class);
	}

	public function testConfigMethod() {
		$container = new Container();

		$rules = [BaseBinding::class => AliasBinding::class];
		$container->configMethod(ConfigMethod::class, 'run', $rules);
		list ($instance, $args) = $container->getMethod(ConfigMethod::class, 'run');

		$this->assertInstanceOf(AliasBinding::class, $args[0]);
	}

	public function testConfigMethodWithInvalidRule() {
		$this->expectException(InvalidRuleException::class);

		$container = new Container();

		$rules = [BaseBinding::class => 1];
		$container->configMethod(ConfigMethod::class, 'run', $rules);
		$container->getMethod(ConfigMethod::class, 'run');
	}

	public function testMethodInjectionWithConstructor() {
		$container = new Container();

		$results = $container->getMethod(MethodInjectionWithConstructor::class, 'injectB');
		$this->assertTrue(is_array($results));
		list ($instance1, $args) = $results;
		$this->assertInstanceOf(DependencyA::class, $instance1->a);
		$this->assertInstanceOf(DependencyB::class, $args[0]);

		$results = $container->getMethod(MethodInjectionWithConstructor::class, 'injectC');
		$this->assertTrue(is_array($results));
		list ($instance2, $args) = $results;
		$this->assertSame($instance1, $instance2);
		$this->assertInstanceOf(DependencyC::class, $args[0]);
	}

	public function testMethodInjectionWithoutConstructor() {
		$container = new Container();

		$results = $container->getMethod(MethodInjectionWithoutConstructor::class, 'injectB');
		$this->assertTrue(is_array($results));
		list ($instance1, $args) = $results;
		$this->assertInstanceOf(DependencyB::class, $args[0]);

		$results = $container->getMethod(MethodInjectionWithoutConstructor::class, 'injectC');
		$this->assertTrue(is_array($results));
		list ($instance2, $args) = $results;
		$this->assertSame($instance1, $instance2);
		$this->assertInstanceOf(DependencyC::class, $args[0]);
	}

	public function testMethodInjectionWithExtraMappings() {
		$container = new Container();
		$next = function () {};

		list ($instance, $args) = $container->getMethod(
			MethodInjectionWithExtraMappings::class,
			'injectA',
			['Closure' => $next]
		);

		$this->assertInstanceOf(DependencyA::class, $args[0]);
		$this->assertSame($next, $args[1]);
	}
}
