<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\Tests\Service;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\BizRoleBundle\Entity\BizRole;
use Tourze\BizRoleBundle\Repository\BizRoleRepository;
use Tourze\BizRoleBundle\Service\BizRoleQueryService;

/**
 * @internal
 */
#[CoversClass(BizRoleQueryService::class)]
final class BizRoleQueryServiceTest extends TestCase
{
    private BizRoleQueryService $bizRoleQueryService;

    private BizRoleRepository&MockObject $bizRoleRepository;

    protected function setUp(): void
    {
        $this->bizRoleRepository = $this->createMock(BizRoleRepository::class);
        $this->bizRoleQueryService = new BizRoleQueryService($this->bizRoleRepository);
    }

    public function testGetValidRoles(): void
    {
        // 创建测试数据
        $role1 = new BizRole();
        $role1->setName('admin');
        $role1->setTitle('管理员');
        $role1->setValid(true);

        $role2 = new BizRole();
        $role2->setName('user');
        $role2->setTitle('普通用户');
        $role2->setValid(true);

        $expectedRoles = [$role1, $role2];

        // 配置 mock
        $this->bizRoleRepository
            ->expects($this->once())
            ->method('loadValidRoles')
            ->willReturn($expectedRoles)
        ;

        // 执行测试
        $result = $this->bizRoleQueryService->getValidRoles();

        // 验证结果
        $this->assertEquals($expectedRoles, $result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(BizRole::class, $result);
    }

    public function testSearchRoles(): void
    {
        // 创建测试数据
        $role1 = new BizRole();
        $role1->setName('admin');
        $role1->setTitle('系统管理员');
        $role1->setValid(true);

        $expectedRoles = [$role1];
        $searchQuery = '管理员';

        // 创建 QueryBuilder mock
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        // 配置 QueryBuilder chain
        $queryBuilder->expects($this->once())
            ->method('where')
            ->with('r.title LIKE :query OR r.name LIKE :query')
            ->willReturnSelf()
        ;

        $queryBuilder->expects($this->once())
            ->method('andWhere')
            ->with('r.valid = true')
            ->willReturnSelf()
        ;

        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('query', '%管理员%')
            ->willReturnSelf()
        ;

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query)
        ;

        $query->expects($this->once())
            ->method('getResult')
            ->willReturn($expectedRoles)
        ;

        // 配置 Repository mock
        $this->bizRoleRepository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->with('r')
            ->willReturn($queryBuilder)
        ;

        // 执行测试
        $result = $this->bizRoleQueryService->searchRoles($searchQuery);

        // 验证结果
        $this->assertEquals($expectedRoles, $result);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(BizRole::class, $result);
    }

    public function testSearchRolesWithEmptyQuery(): void
    {
        // 测试空查询字符串
        $expectedRoles = [];
        $searchQuery = '';

        // 创建 QueryBuilder mock
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        // 配置 QueryBuilder chain
        $queryBuilder->expects($this->once())
            ->method('where')
            ->with('r.title LIKE :query OR r.name LIKE :query')
            ->willReturnSelf()
        ;

        $queryBuilder->expects($this->once())
            ->method('andWhere')
            ->with('r.valid = true')
            ->willReturnSelf()
        ;

        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('query', '%%')
            ->willReturnSelf()
        ;

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query)
        ;

        $query->expects($this->once())
            ->method('getResult')
            ->willReturn($expectedRoles)
        ;

        // 配置 Repository mock
        $this->bizRoleRepository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->with('r')
            ->willReturn($queryBuilder)
        ;

        // 执行测试
        $result = $this->bizRoleQueryService->searchRoles($searchQuery);

        // 验证结果
        $this->assertEquals($expectedRoles, $result);
        $this->assertIsArray($result);
    }

    public function testFormatRolesForAutocomplete(): void
    {
        // 创建测试数据
        $role1 = new BizRole();
        $role1->setName('admin');
        $role1->setTitle('系统管理员');
        // 模拟 ID 设置（通常由 Doctrine 设置）
        $reflection = new \ReflectionClass($role1);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($role1, 1);

        $role2 = new BizRole();
        $role2->setName('user');
        $role2->setTitle('普通用户');
        $reflection = new \ReflectionClass($role2);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($role2, 2);

        $roles = [$role1, $role2];

        // 预期结果
        $expectedResult = [
            [
                'id' => 1,
                'text' => '系统管理员 (admin)',
            ],
            [
                'id' => 2,
                'text' => '普通用户 (user)',
            ],
        ];

        // 执行测试
        $result = $this->bizRoleQueryService->formatRolesForAutocomplete($roles);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        $this->assertCount(2, $result);

        // 验证每个元素的结构
        foreach ($result as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('text', $item);
            $this->assertIsInt($item['id']);
            $this->assertIsString($item['text']);
        }
    }

    public function testFormatRolesForAutocompleteWithEmptyArray(): void
    {
        // 测试空数组
        $roles = [];
        $expectedResult = [];

        // 执行测试
        $result = $this->bizRoleQueryService->formatRolesForAutocomplete($roles);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFormatRolesForAutocompleteWithDefaultId(): void
    {
        // 测试新实体（ID 为默认值 0）
        $role = new BizRole();
        $role->setName('new-role');
        $role->setTitle('新角色');
        // ID 默认为 0

        $roles = [$role];
        $expectedResult = [
            [
                'id' => 0,
                'text' => '新角色 (new-role)',
            ],
        ];

        // 执行测试
        $result = $this->bizRoleQueryService->formatRolesForAutocomplete($roles);

        // 验证结果
        $this->assertEquals($expectedResult, $result);
        $this->assertCount(1, $result);
        $this->assertEquals(0, $result[0]['id']);
        $this->assertEquals('新角色 (new-role)', $result[0]['text']);
    }

    public function testConstructorDependencyInjection(): void
    {
        // 验证构造函数正确注入了依赖
        $this->assertInstanceOf(BizRoleQueryService::class, $this->bizRoleQueryService);
    }

    public function testServiceMethodsReturnTypes(): void
    {
        // 测试方法返回类型

        // getValidRoles 应该返回数组
        $this->bizRoleRepository
            ->expects($this->once())
            ->method('loadValidRoles')
            ->willReturn([])
        ;

        $result = $this->bizRoleQueryService->getValidRoles();
        $this->assertIsArray($result);

        // formatRolesForAutocomplete 应该返回特定格式的数组
        $result = $this->bizRoleQueryService->formatRolesForAutocomplete([]);
        $this->assertIsArray($result);
    }
}
