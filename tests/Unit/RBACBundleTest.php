<?php

namespace Tourze\RBACBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\RBACBundle\RBACBundle;

class RBACBundleTest extends TestCase
{
    public function testBundleInheritance(): void
    {
        $bundle = new RBACBundle();

        $this->assertInstanceOf(Bundle::class, $bundle);
    }

    public function testBundleCanBeInstantiated(): void
    {
        $bundle = new RBACBundle();

        $this->assertNotNull($bundle);
    }

    public function testBundleName(): void
    {
        $bundle = new RBACBundle();
        $name = $bundle->getName();

        $this->assertEquals('RBACBundle', $name);
    }

    public function testGetPath(): void
    {
        $bundle = new RBACBundle();
        $path = $bundle->getPath();

        $this->assertNotEmpty($path);
        $this->assertDirectoryExists($path);
        $this->assertStringEndsWith('src', $path);
    }
}
