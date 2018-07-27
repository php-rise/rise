<?php
namespace Rise\Test;

use PHPUnit\Framework\TestCase;
use Rise\Path;

final class PathTest extends TestCase {
	public function testPaths() {
		$path = new Path();
		$projectRoot = __DIR__;
		$path->setProjectRootPath($projectRoot);
		$this->assertSame($projectRoot, $path->getProjectRootPath());
		$this->assertSame($projectRoot . '/config', $path->getConfigPath());
		$this->assertSame($projectRoot . '/public', $path->getPublicPath());
		$this->assertSame($projectRoot . '/logs', $path->getLogsPath());
		$this->assertSame($projectRoot . '/templates', $path->getTemplatesPath());
		$this->assertSame($projectRoot . '/migrations', $path->getMigrationsPath());
	}
}
