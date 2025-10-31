<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\BizRoleBundle\Entity\BizRole;
use Tourze\BizRoleBundle\Entity\RoleEntityPermission;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

/**
 * 业务用户系统菜单服务
 */
#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('用户模块')) {
            $item->addChild('用户模块');
        }

        $userMenu = $item->getChild('用户模块');

        if (null !== $userMenu) {
            // 角色管理菜单
            $userMenu->addChild('角色管理')
                ->setUri($this->linkGenerator->getCurdListPage(BizRole::class))
                ->setAttribute('icon', 'fas fa-user-tag')
            ;
            // 数据权限管理菜单
            $userMenu->addChild('数据权限管理')
                ->setUri($this->linkGenerator->getCurdListPage(RoleEntityPermission::class))
                ->setAttribute('icon', 'fas fa-shield-alt')
            ;
        }
    }
}
