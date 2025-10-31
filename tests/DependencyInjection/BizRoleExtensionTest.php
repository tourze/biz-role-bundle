<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\BizRoleBundle\DependencyInjection\BizRoleExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(BizRoleExtension::class)]
final class BizRoleExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    public function testLoadWithEmptyConfiguration(): void
    {
        $configs = [];
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');

        $extension = new BizRoleExtension();
        $extension->load($configs, $container);

        $this->assertTrue($container->hasDefinition('Tourze\BizRoleBundle\Service\AdminMenu'));
    }

    public function testGetAlias(): void
    {
        $extension = new BizRoleExtension();
        $alias = $extension->getAlias();
        $this->assertEquals('biz_role', $alias);
    }
}
