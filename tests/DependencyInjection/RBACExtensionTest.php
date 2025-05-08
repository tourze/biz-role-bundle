<?php

namespace Tourze\RBACBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\RBACBundle\DependencyInjection\RBACExtension;

class RBACExtensionTest extends TestCase
{
    private RBACExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new RBACExtension();
        $this->container = new ContainerBuilder();
    }

    public function testLoadWithEmptyConfiguration(): void
    {
        // 测试加载空配置
        $this->extension->load([], $this->container);

        // 确保容器已编译
        $this->container->compile();

        // 验证服务定义已加载（如果未来有服务，可以在这里验证）
        $this->assertTrue(true, 'Extension loaded successfully');
    }

    public function testFileLocatorPathIsValid(): void
    {
        // 这个测试是确保文件定位器路径有效
        // 如果路径无效，load方法会抛出异常

        $this->expectNotToPerformAssertions();
        $this->extension->load([], $this->container);
    }

    public function testServiceDefinitions(): void
    {
        // 加载服务定义
        $this->extension->load([], $this->container);

        // 验证服务配置文件成功加载
        // 目前services.yaml是空的，所以我们只能验证没有抛出异常
        $this->assertTrue(true, 'Services configured successfully');

        // 如果未来添加服务定义，可以在这里添加更多断言
    }
}
