<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\BizRoleBundle\Entity\BizRole;
use Tourze\BizRoleBundle\Service\BizRoleQueryService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(BizRoleQueryService::class)]
#[RunTestsInSeparateProcesses]
final class BizRoleQueryServiceTest extends AbstractIntegrationTestCase
{
    private BizRoleQueryService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(BizRoleQueryService::class);
    }

    public function testGetValidRoles(): void
    {
        // 创建有效角色
        $role1 = new BizRole();
        $role1->setName('admin_' . uniqid());
        $role1->setTitle('管理员');
        $role1->setValid(true);

        $role2 = new BizRole();
        $role2->setName('user_' . uniqid());
        $role2->setTitle('普通用户');
        $role2->setValid(true);

        // 创建无效角色
        $role3 = new BizRole();
        $role3->setName('disabled_' . uniqid());
        $role3->setTitle('已禁用角色');
        $role3->setValid(false);

        // 持久化到数据库
        self::getEntityManager()->persist($role1);
        self::getEntityManager()->persist($role2);
        self::getEntityManager()->persist($role3);
        self::getEntityManager()->flush();

        // 执行测试
        $result = $this->service->getValidRoles();

        // 验证结果 - 只返回有效角色
        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(BizRole::class, $result);

        // 验证包含我们创建的有效角色
        $validRoleIds = array_map(fn (BizRole $role) => $role->getId(), $result);
        $this->assertContains($role1->getId(), $validRoleIds);
        $this->assertContains($role2->getId(), $validRoleIds);
        $this->assertNotContains($role3->getId(), $validRoleIds);
    }

    public function testSearchRoles(): void
    {
        // 创建测试角色
        $role1 = new BizRole();
        $role1->setName('admin_search_' . uniqid());
        $role1->setTitle('系统管理员');
        $role1->setValid(true);

        $role2 = new BizRole();
        $role2->setName('user_search_' . uniqid());
        $role2->setTitle('普通用户');
        $role2->setValid(true);

        $role3 = new BizRole();
        $role3->setName('operator_search_' . uniqid());
        $role3->setTitle('操作员');
        $role3->setValid(true);

        // 持久化
        self::getEntityManager()->persist($role1);
        self::getEntityManager()->persist($role2);
        self::getEntityManager()->persist($role3);
        self::getEntityManager()->flush();

        // 执行搜索 - 搜索"管理员"
        $result = $this->service->searchRoles('管理员');

        // 验证结果
        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(BizRole::class, $result);

        // 验证包含匹配的角色
        $foundRole1 = false;
        foreach ($result as $role) {
            if ($role->getId() === $role1->getId()) {
                $foundRole1 = true;
                break;
            }
        }
        $this->assertTrue($foundRole1, '应该找到"系统管理员"角色');

        // 搜索 name 字段
        $result2 = $this->service->searchRoles('admin_search');
        $this->assertIsArray($result2);

        $foundByName = false;
        foreach ($result2 as $role) {
            if ($role->getId() === $role1->getId()) {
                $foundByName = true;
                break;
            }
        }
        $this->assertTrue($foundByName, '应该能通过name字段搜索到角色');
    }

    public function testSearchRolesWithEmptyQuery(): void
    {
        // 创建测试角色
        $role1 = new BizRole();
        $role1->setName('empty_query_test_' . uniqid());
        $role1->setTitle('空查询测试角色');
        $role1->setValid(true);

        self::getEntityManager()->persist($role1);
        self::getEntityManager()->flush();

        // 执行空查询
        $result = $this->service->searchRoles('');

        // 验证结果 - 空查询应该返回所有有效角色
        $this->assertIsArray($result);

        // 验证至少包含我们创建的角色
        $roleIds = array_map(fn (BizRole $role) => $role->getId(), $result);
        $this->assertContains($role1->getId(), $roleIds);
    }

    public function testFormatRolesForAutocomplete(): void
    {
        // 创建测试角色
        $role1 = new BizRole();
        $role1->setName('admin_format_' . uniqid());
        $role1->setTitle('系统管理员');
        $role1->setValid(true);

        $role2 = new BizRole();
        $role2->setName('user_format_' . uniqid());
        $role2->setTitle('普通用户');
        $role2->setValid(true);

        // 持久化
        self::getEntityManager()->persist($role1);
        self::getEntityManager()->persist($role2);
        self::getEntityManager()->flush();

        // 刷新实体以确保ID已设置
        self::getEntityManager()->refresh($role1);
        self::getEntityManager()->refresh($role2);

        // 执行格式化
        $roles = [$role1, $role2];
        $result = $this->service->formatRolesForAutocomplete($roles);

        // 验证结果结构
        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        // 验证第一个角色的格式
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('text', $result[0]);
        $this->assertEquals($role1->getId(), $result[0]['id']);
        $this->assertEquals('系统管理员 (admin_format_' . substr($role1->getName(), -13) . ')', $result[0]['text']);

        // 验证第二个角色的格式
        $this->assertArrayHasKey('id', $result[1]);
        $this->assertArrayHasKey('text', $result[1]);
        $this->assertEquals($role2->getId(), $result[1]['id']);
        $this->assertStringContainsString('普通用户', $result[1]['text']);

        // 验证所有元素的数据类型
        foreach ($result as $item) {
            $this->assertIsInt($item['id']);
            $this->assertIsString($item['text']);
            $this->assertGreaterThan(0, $item['id']);
        }
    }

    public function testFormatRolesForAutocompleteWithEmptyArray(): void
    {
        // 测试空数组
        $result = $this->service->formatRolesForAutocomplete([]);

        // 验证结果
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFindOrCreate(): void
    {
        $uniqueName = 'test_role_' . uniqid();
        $title = '测试角色';

        // 第一次调用 - 应该创建新角色
        $role1 = $this->service->findOrCreate($uniqueName, $title);

        $this->assertInstanceOf(BizRole::class, $role1);
        $this->assertEquals($uniqueName, $role1->getName());
        $this->assertEquals($title, $role1->getTitle());
        $this->assertTrue($role1->isValid());
        $this->assertGreaterThan(0, $role1->getId());

        // 第二次调用 - 应该返回已存在的角色
        $role2 = $this->service->findOrCreate($uniqueName, $title);

        $this->assertInstanceOf(BizRole::class, $role2);
        $this->assertEquals($role1->getId(), $role2->getId());
        $this->assertEquals($uniqueName, $role2->getName());
        $this->assertEquals($title, $role2->getTitle());
    }

    public function testFindOrCreateWithoutTitle(): void
    {
        $uniqueName = 'test_role_no_title_' . uniqid();

        // 不提供 title，应该使用 name 作为 title
        $role = $this->service->findOrCreate($uniqueName);

        $this->assertInstanceOf(BizRole::class, $role);
        $this->assertEquals($uniqueName, $role->getName());
        $this->assertEquals($uniqueName, $role->getTitle());
        $this->assertTrue($role->isValid());
        $this->assertGreaterThan(0, $role->getId());
    }
}
