<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\DataFixtures;

use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\BizRoleBundle\Entity\BizRole;
use Tourze\UserServiceContracts\UserServiceConstants;

/**
 * 角色数据填充
 *
 * 创建系统中各种预定义角色，包含不同权限组合
 * 只在 test 和 dev 环境中加载
 */
#[When(env: 'test')]
#[When(env: 'dev')]
class BizRoleFixtures extends Fixture implements FixtureGroupInterface
{
    // 角色引用常量
    public const ADMIN_ROLE_REFERENCE = 'admin-role';
    public const MODERATOR_ROLE_REFERENCE = 'moderator-role';
    public const USER_ROLE_REFERENCE = 'user-role';
    public const CONTENT_MANAGER_ROLE_REFERENCE = 'content-manager-role';
    public const REPORT_VIEWER_ROLE_REFERENCE = 'report-viewer-role';
    public const ANALYST_ROLE_REFERENCE = 'analyst-role';

    // 角色名称常量
    private const ROLE_ADMIN = 'ROLE_ADMIN';
    private const ROLE_MODERATOR = 'ROLE_MODERATOR';
    private const ROLE_USER = 'ROLE_USER';
    private const ROLE_CONTENT_MANAGER = 'ROLE_CONTENT_MANAGER';
    private const ROLE_REPORT_VIEWER = 'ROLE_REPORT_VIEWER';
    private const ROLE_ANALYST = 'ROLE_ANALYST';

    public function load(ObjectManager $manager): void
    {
        // 创建管理员角色
        $adminRole = new BizRole();
        $adminRole->setName(self::ROLE_ADMIN);
        $adminRole->setTitle('系统管理员');
        $adminRole->setAdmin(true);
        $adminRole->setValid(true);
        $adminRole->setPermissions(['admin', 'user_manage', 'role_manage', 'content_manage', 'system_config']);
        $adminRole->setCreateTime(CarbonImmutable::now()->modify('-60 days'));
        $adminRole->setUpdateTime(CarbonImmutable::now()->modify('-30 days'));
        $manager->persist($adminRole);
        $this->addReference(self::ADMIN_ROLE_REFERENCE, $adminRole);

        // 创建内容审核员角色
        $moderatorRole = new BizRole();
        $moderatorRole->setName(self::ROLE_MODERATOR);
        $moderatorRole->setTitle('内容审核员');
        $moderatorRole->setAdmin(false);
        $moderatorRole->setValid(true);
        $moderatorRole->setPermissions(['content_audit', 'report_view', 'keyword_manage']);
        $moderatorRole->setCreateTime(CarbonImmutable::now()->modify('-55 days'));
        $moderatorRole->setUpdateTime(CarbonImmutable::now()->modify('-25 days'));
        $manager->persist($moderatorRole);
        $this->addReference(self::MODERATOR_ROLE_REFERENCE, $moderatorRole);

        // 创建普通用户角色
        $userRole = new BizRole();
        $userRole->setName(self::ROLE_USER);
        $userRole->setTitle('普通用户');
        $userRole->setAdmin(false);
        $userRole->setValid(true);
        $userRole->setPermissions(['profile_view', 'content_create']);
        $userRole->setCreateTime(CarbonImmutable::now()->modify('-50 days'));
        $userRole->setUpdateTime(CarbonImmutable::now()->modify('-20 days'));
        $manager->persist($userRole);
        $this->addReference(self::USER_ROLE_REFERENCE, $userRole);

        // 创建内容管理员角色
        $contentManagerRole = new BizRole();
        $contentManagerRole->setName(self::ROLE_CONTENT_MANAGER);
        $contentManagerRole->setTitle('内容管理员');
        $contentManagerRole->setAdmin(false);
        $contentManagerRole->setValid(true);
        $contentManagerRole->setPermissions(['content_manage', 'content_delete', 'content_edit']);
        $contentManagerRole->setCreateTime(CarbonImmutable::now()->modify('-45 days'));
        $contentManagerRole->setUpdateTime(CarbonImmutable::now()->modify('-15 days'));
        $manager->persist($contentManagerRole);
        $this->addReference(self::CONTENT_MANAGER_ROLE_REFERENCE, $contentManagerRole);

        // 创建报告查看者角色
        $reportViewerRole = new BizRole();
        $reportViewerRole->setName(self::ROLE_REPORT_VIEWER);
        $reportViewerRole->setTitle('报告查看者');
        $reportViewerRole->setAdmin(false);
        $reportViewerRole->setValid(true);
        $reportViewerRole->setPermissions(['report_view', 'statistics_view']);
        $reportViewerRole->setCreateTime(CarbonImmutable::now()->modify('-40 days'));
        $reportViewerRole->setUpdateTime(CarbonImmutable::now()->modify('-10 days'));
        $manager->persist($reportViewerRole);
        $this->addReference(self::REPORT_VIEWER_ROLE_REFERENCE, $reportViewerRole);

        // 创建数据分析师角色
        $analystRole = new BizRole();
        $analystRole->setName(self::ROLE_ANALYST);
        $analystRole->setTitle('数据分析师');
        $analystRole->setAdmin(false);
        $analystRole->setValid(true);
        $analystRole->setPermissions(['report_view', 'statistics_view', 'data_export', 'data_analysis']);
        $analystRole->setCreateTime(CarbonImmutable::now()->modify('-35 days'));
        $analystRole->setUpdateTime(CarbonImmutable::now()->modify('-5 days'));
        $manager->persist($analystRole);
        $this->addReference(self::ANALYST_ROLE_REFERENCE, $analystRole);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return [
            UserServiceConstants::USER_FIXTURES_NAME,
        ];
    }
}
