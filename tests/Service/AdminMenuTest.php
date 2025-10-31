<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\BizRoleBundle\Service\AdminMenu;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private AdminMenu $adminMenu;

    private LinkGeneratorInterface $linkGenerator;

    protected function onSetUp(): void
    {
        // Mock the LinkGeneratorInterface
        $this->linkGenerator = $this->createMock(LinkGeneratorInterface::class);

        // Replace the service in the container
        self::getContainer()->set(LinkGeneratorInterface::class, $this->linkGenerator);

        // Get the AdminMenu service from the container
        $this->adminMenu = self::getService(AdminMenu::class);
    }

    public function testInvokeWithSimpleMenu(): void
    {
        // 使用简化的测试方法，避免复杂接口的 Mock
        $item = $this->createMock(ItemInterface::class);
        $userModuleItem = $this->createMock(ItemInterface::class);

        // 第一次调用 getChild 返回 null（检查是否存在），第二次返回创建的子菜单
        $item->expects($this->exactly(2))
            ->method('getChild')
            ->with('用户模块')
            ->willReturnOnConsecutiveCalls(null, $userModuleItem)
        ;

        $item->expects($this->once())
            ->method('addChild')
            ->with('用户模块')
            ->willReturn($userModuleItem)
        ;

        // 配置用户模块子菜单的行为
        $userModuleItem->expects($this->exactly(2))
            ->method('addChild')
            ->willReturnSelf()
        ;

        $userModuleItem->expects($this->exactly(2))
            ->method('setUri')
            ->willReturnSelf()
        ;

        $userModuleItem->expects($this->exactly(2))
            ->method('setAttribute')
            ->willReturnSelf()
        ;

        // 配置链接生成器返回测试 URL
        $this->linkGenerator->expects($this->atLeastOnce())
            ->method('getCurdListPage')
            ->willReturn('/admin/test')
        ;

        // 执行测试 - 主要验证不会抛出异常
        ($this->adminMenu)($item);

        // 验证所有的 expects 设置都被调用了
    }

    public function testInvokeHandlesExistingUserModule(): void
    {
        $item = $this->createMock(ItemInterface::class);
        $userModuleItem = $this->createMock(ItemInterface::class);

        // 模拟用户模块已存在的情况 - 两次调用都返回相同的子菜单
        $item->expects($this->exactly(2))
            ->method('getChild')
            ->with('用户模块')
            ->willReturn($userModuleItem)
        ;

        // 不会调用 addChild，因为菜单已存在
        $item->expects($this->never())
            ->method('addChild')
        ;

        // 配置用户模块子菜单的行为
        $userModuleItem->expects($this->exactly(2))
            ->method('addChild')
            ->willReturnSelf()
        ;

        $userModuleItem->expects($this->exactly(2))
            ->method('setUri')
            ->willReturnSelf()
        ;

        $userModuleItem->expects($this->exactly(2))
            ->method('setAttribute')
            ->willReturnSelf()
        ;

        // 配置链接生成器
        $this->linkGenerator->expects($this->atLeastOnce())
            ->method('getCurdListPage')
            ->willReturn('/admin/test')
        ;

        // 执行测试
        ($this->adminMenu)($item);

        // 验证所有的 expects 设置都被调用了
    }
}
