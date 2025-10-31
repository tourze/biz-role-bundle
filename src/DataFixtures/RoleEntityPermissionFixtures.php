<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\DataFixtures;

use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\BizRoleBundle\Entity\BizRole;
use Tourze\BizRoleBundle\Entity\RoleEntityPermission;
use Tourze\UserServiceContracts\UserServiceConstants;

#[When(env: 'test')]
#[When(env: 'dev')]
class RoleEntityPermissionFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const ADMIN_USER_PERMISSION_REFERENCE = 'admin-user-permission';
    public const ADMIN_ROLE_PERMISSION_REFERENCE = 'admin-role-permission';
    public const MODERATOR_CONTENT_PERMISSION_REFERENCE = 'moderator-content-permission';
    public const ANALYST_REPORT_PERMISSION_REFERENCE = 'analyst-report-permission';

    public function load(ObjectManager $manager): void
    {
        $adminRole = $this->getReference(BizRoleFixtures::ADMIN_ROLE_REFERENCE, BizRole::class);
        assert($adminRole instanceof BizRole);
        $moderatorRole = $this->getReference(BizRoleFixtures::MODERATOR_ROLE_REFERENCE, BizRole::class);
        assert($moderatorRole instanceof BizRole);
        $analystRole = $this->getReference(BizRoleFixtures::ANALYST_ROLE_REFERENCE, BizRole::class);
        assert($analystRole instanceof BizRole);

        // 管理员用户数据权限
        $adminUserPermission = new RoleEntityPermission();
        $adminUserPermission->setRole($adminRole);
        $adminUserPermission->setEntityClass('App\Entity\User');
        $adminUserPermission->setStatement('1 = 1');
        $adminUserPermission->setRemark('管理员可以访问所有用户数据');
        $adminUserPermission->setValid(true);
        $adminUserPermission->setCreateTime(CarbonImmutable::now()->modify('-30 days'));
        $adminUserPermission->setUpdateTime(CarbonImmutable::now()->modify('-10 days'));
        $manager->persist($adminUserPermission);
        $this->addReference(self::ADMIN_USER_PERMISSION_REFERENCE, $adminUserPermission);

        // 管理员角色数据权限
        $adminRolePermission = new RoleEntityPermission();
        $adminRolePermission->setRole($adminRole);
        $adminRolePermission->setEntityClass('Tourze\BizRoleBundle\Entity\BizRole');
        $adminRolePermission->setStatement('1 = 1');
        $adminRolePermission->setRemark('管理员可以管理所有角色');
        $adminRolePermission->setValid(true);
        $adminRolePermission->setCreateTime(CarbonImmutable::now()->modify('-25 days'));
        $adminRolePermission->setUpdateTime(CarbonImmutable::now()->modify('-8 days'));
        $manager->persist($adminRolePermission);
        $this->addReference(self::ADMIN_ROLE_PERMISSION_REFERENCE, $adminRolePermission);

        // 审核员内容数据权限
        $moderatorContentPermission = new RoleEntityPermission();
        $moderatorContentPermission->setRole($moderatorRole);
        $moderatorContentPermission->setEntityClass('App\Entity\Content');
        $moderatorContentPermission->setStatement('status = "pending" OR status = "published"');
        $moderatorContentPermission->setRemark('审核员只能查看待审核和已发布的内容');
        $moderatorContentPermission->setValid(true);
        $moderatorContentPermission->setCreateTime(CarbonImmutable::now()->modify('-20 days'));
        $moderatorContentPermission->setUpdateTime(CarbonImmutable::now()->modify('-5 days'));
        $manager->persist($moderatorContentPermission);
        $this->addReference(self::MODERATOR_CONTENT_PERMISSION_REFERENCE, $moderatorContentPermission);

        // 分析师报告数据权限
        $analystReportPermission = new RoleEntityPermission();
        $analystReportPermission->setRole($analystRole);
        $analystReportPermission->setEntityClass('App\Entity\Report');
        $analystReportPermission->setStatement('department_id IN (1, 2, 3) AND created_at >= "2024-01-01"');
        $analystReportPermission->setRemark('分析师可以查看特定部门的最新报告');
        $analystReportPermission->setValid(true);
        $analystReportPermission->setCreateTime(CarbonImmutable::now()->modify('-15 days'));
        $analystReportPermission->setUpdateTime(CarbonImmutable::now()->modify('-3 days'));
        $manager->persist($analystReportPermission);
        $this->addReference(self::ANALYST_REPORT_PERMISSION_REFERENCE, $analystReportPermission);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            BizRoleFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return [
            UserServiceConstants::USER_FIXTURES_NAME,
        ];
    }
}
