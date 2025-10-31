<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BizRoleBundle\Entity\BizRole;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DoctrineResolveTargetEntityBundle\DependencyInjection\Compiler\ResolveTargetEntityPass;
use Tourze\DoctrineResolveTargetEntityBundle\DoctrineResolveTargetEntityBundle;
use Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle;
use Tourze\EasyAdminMenuBundle\EasyAdminMenuBundle;
use Tourze\RBAC\Core\Level0\Role;

class BizRoleBundle extends Bundle implements BundleDependencyInterface
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(
            new ResolveTargetEntityPass(Role::class, BizRole::class),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            1000,
        );
    }

    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            DoctrineResolveTargetEntityBundle::class => ['all' => true],
            DoctrineTimestampBundle::class => ['all' => true],
            EasyAdminMenuBundle::class => ['all' => true],
        ];
    }
}
