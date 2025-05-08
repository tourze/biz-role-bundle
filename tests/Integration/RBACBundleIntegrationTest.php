<?php

namespace Tourze\RBACBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tourze\RBACBundle\RBACBundle;

class RBACBundleIntegrationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return IntegrationTestKernel::class;
    }

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testBundleRegistration(): void
    {
        $kernel = self::$kernel;
        $container = static::getContainer();

        $bundles = $kernel->getBundles();

        // 检查是否有RBACBundle实例在bundles数组中
        $rbacBundleFound = false;
        foreach ($bundles as $bundle) {
            if ($bundle instanceof RBACBundle) {
                $rbacBundleFound = true;
                break;
            }
        }

        $this->assertTrue($rbacBundleFound, 'RBACBundle should be registered');
    }

    public function testServiceConfigurationLoaded(): void
    {
        $container = static::getContainer();

        // 检查依赖注入容器是否存在
        $this->assertNotNull($container);

        // 由于当前服务配置为空，我们只能验证bundle被正确加载
        // 未来如果添加服务，可以在这里测试服务是否正确注册
    }
}
