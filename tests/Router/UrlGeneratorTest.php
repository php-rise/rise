<?php
namespace Rise\Test\Router;

use PHPUnit\Framework\TestCase;
use Rise\Router\UrlGenerator;

final class UrlGeneratorTest extends TestCase {
	public function setUp() {
		$_SERVER['HTTP_HOST'] = 'www.example.com';
	}

	public function tearDown() {
		unset($_SERVER['HTTP_HOST']);
	}

	public function testOutput() {
		$urlGenerator = new UrlGenerator();

		$urlGenerator->add('root', '/');
		$urlGenerator->add('product.list', '/products');
		$urlGenerator->add('product.show', '/products/{id}');

		$this->assertSame('http://www.example.com/', $urlGenerator->generate('root'));
		$this->assertSame(
			'http://www.example.com/products',
			$urlGenerator->generate('product.list')
		);
		$this->assertSame(
			'http://www.example.com/products/15',
			$urlGenerator->generate('product.show', ['id' => '15'])
		);
		$this->assertSame(
			'',
			$urlGenerator->generate('not.exists')
		);
	}
}
