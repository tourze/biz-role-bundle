<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\BizRoleBundle\Entity\BizRole;
use Tourze\BizRoleBundle\Entity\RoleEntityPermission;
use Tourze\BizRoleBundle\Repository\RoleEntityPermissionRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(RoleEntityPermissionRepository::class)]
#[RunTestsInSeparateProcesses]
final class RoleEntityPermissionRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // Repository测试的初始化逻辑（如有需要）
    }

    protected function getRepository(): RoleEntityPermissionRepository
    {
        return self::getService(RoleEntityPermissionRepository::class);
    }

    public function testRepositoryCanHandleRoleEntityPermissionEntity(): void
    {
        $permission = new RoleEntityPermission();
        $permission->setStatement('id = :userId');

        $this->assertEquals('id = :userId', $permission->getStatement());
    }

    public function testSave(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $role = $this->createTestRole('editor', '编辑者');

        // 创建新的权限实体
        $permission = new RoleEntityPermission();
        $permission->setRole($role);
        $permission->setEntityClass('App\Entity\Article');
        $permission->setStatement('authorId = :userId');
        $permission->setRemark('文章编辑权限');
        $permission->setValid(true);
        $permission->setCreatedBy('admin');

        // 测试保存
        $repository->save($permission);

        $this->assertNotNull($permission->getId());
        $this->assertEntityPersisted($permission);
    }

    public function testRemove(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $role = $this->createTestRole('moderator', '版主');

        // 创建并保存权限
        $permission = new RoleEntityPermission();
        $permission->setRole($role);
        $permission->setEntityClass('App\Entity\Comment');
        $permission->setStatement('status = "approved"');
        $permission->setValid(true);

        $this->persistAndFlush($permission);
        $permissionId = $permission->getId();

        // 确保权限已保存
        $this->assertNotNull($permissionId);

        // 测试删除
        $repository->remove($permission);

        // 验证权限已从数据库中删除
        $this->assertEntityNotExists(RoleEntityPermission::class, $permissionId);
    }

    public function testRoleEntityPermissionEntityHasExpectedMethods(): void
    {
        $permission = new RoleEntityPermission();

        // Test functionality instead of method existence
        $permission->setStatement('test statement');
        $this->assertEquals('test statement', $permission->getStatement());
    }

    public function testRoleEntityPermissionStringRepresentation(): void
    {
        $permission = new RoleEntityPermission();
        $permission->setStatement('id = 1');

        $this->assertEquals('id = 1', $permission->getStatement());
    }

    public function testRoleEntityPermissionTimestamps(): void
    {
        $permission = new RoleEntityPermission();
        $now = new \DateTimeImmutable();

        // Test functionality instead of method existence
        $permission->setCreateTime($now);
        $this->assertEquals($now, $permission->getCreateTime());
    }

    public function testRoleEntityPermissionValidation(): void
    {
        $permission = new RoleEntityPermission();

        $permission->setValid(true);
        $this->assertTrue($permission->isValid());

        $permission->setValid(false);
        $this->assertFalse($permission->isValid());
    }

    public function testRoleEntityPermissionUserTracking(): void
    {
        $permission = new RoleEntityPermission();

        // Test functionality instead of method existence
        $permission->setCreatedBy('test_user');
        $this->assertEquals('test_user', $permission->getCreatedBy());
    }

    public function testRoleEntityPermissionRemark(): void
    {
        $permission = new RoleEntityPermission();

        $permission->setRemark('测试备注');
        $this->assertEquals('测试备注', $permission->getRemark());
    }

    public function testFindByRoleAssociation(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $adminRole = $this->createTestRole('admin', '管理员');
        $userRole = $this->createTestRole('user', '用户');

        // 为管理员角色创建权限
        $adminPermission = new RoleEntityPermission();
        $adminPermission->setRole($adminRole);
        $adminPermission->setEntityClass('App\Entity\User');
        $adminPermission->setStatement('1=1');
        $adminPermission->setValid(true);

        // 为用户角色创建权限
        $userPermission = new RoleEntityPermission();
        $userPermission->setRole($userRole);
        $userPermission->setEntityClass('App\Entity\User');
        $userPermission->setStatement('id = :userId');
        $userPermission->setValid(true);

        $this->persistAndFlush($adminPermission);
        $this->persistAndFlush($userPermission);

        // 清理缓存
        self::getEntityManager()->clear();

        // 测试根据角色查找权限
        $adminPermissions = $repository->findBy(['role' => $adminRole->getId()]);
        $userPermissions = $repository->findBy(['role' => $userRole->getId()]);

        $this->assertCount(1, $adminPermissions);
        $this->assertCount(1, $userPermissions);
        $this->assertEquals('1=1', $adminPermissions[0]->getStatement());
        $this->assertEquals('id = :userId', $userPermissions[0]->getStatement());
    }

    public function testFindByNullableFields(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $role = $this->createTestRole('test', '测试角色');

        // 创建有 remark 的权限
        $permissionWithRemark = new RoleEntityPermission();
        $permissionWithRemark->setRole($role);
        $permissionWithRemark->setEntityClass('App\Entity\TestWithRemark');
        $permissionWithRemark->setStatement('id > 0');
        $permissionWithRemark->setRemark('有备注的权限');
        $permissionWithRemark->setCreatedBy('admin');
        $permissionWithRemark->setUpdatedBy('admin');
        $permissionWithRemark->setValid(true);

        // 创建没有 remark 的权限
        $permissionWithoutRemark = new RoleEntityPermission();
        $permissionWithoutRemark->setRole($role);
        $permissionWithoutRemark->setEntityClass('App\Entity\TestWithoutRemark');
        $permissionWithoutRemark->setStatement('id > 0');
        $permissionWithoutRemark->setRemark(null);
        $permissionWithoutRemark->setCreatedBy(null);
        $permissionWithoutRemark->setUpdatedBy(null);
        $permissionWithoutRemark->setValid(true);

        $this->persistAndFlush($permissionWithRemark);
        $this->persistAndFlush($permissionWithoutRemark);

        // 清理缓存
        self::getEntityManager()->clear();

        // 测试查找有备注的权限（限制在此测试创建的角色范围内）
        $withRemark = $repository->createQueryBuilder('p')
            ->where('p.remark IS NOT NULL')
            ->andWhere('p.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getResult()
        ;

        // 测试查找没有备注的权限（限制在此测试创建的角色范围内）
        $withoutRemark = $repository->createQueryBuilder('p')
            ->where('p.remark IS NULL')
            ->andWhere('p.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getResult()
        ;

        // 测试查找没有创建者的权限（限制在此测试创建的角色范围内）
        $withoutCreatedBy = $repository->createQueryBuilder('p')
            ->where('p.createdBy IS NULL')
            ->andWhere('p.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getResult()
        ;

        self::assertIsArray($withRemark);
        self::assertIsArray($withoutRemark);
        self::assertIsArray($withoutCreatedBy);
        $this->assertCount(1, $withRemark);
        $this->assertCount(1, $withoutRemark);
        $this->assertCount(1, $withoutCreatedBy);
        /** @var RoleEntityPermission $firstWithRemark */
        $firstWithRemark = $withRemark[0];
        $this->assertEquals('有备注的权限', $firstWithRemark->getRemark());
        /** @var RoleEntityPermission $firstWithoutRemark */
        $firstWithoutRemark = $withoutRemark[0];
        $this->assertNull($firstWithoutRemark->getRemark());
    }

    public function testFindOneBySorting(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $role = $this->createTestRole('sorting_test', '排序测试');

        // 创建多个权限，设置不同的时间
        $now = new \DateTimeImmutable();

        $permission1 = new RoleEntityPermission();
        $permission1->setRole($role);
        $permission1->setEntityClass('App\Entity\First');
        $permission1->setStatement('id = 1');
        $permission1->setCreateTime($now->modify('-2 hours'));
        $permission1->setValid(true);

        $permission2 = new RoleEntityPermission();
        $permission2->setRole($role);
        $permission2->setEntityClass('App\Entity\Second');
        $permission2->setStatement('id = 2');
        $permission2->setCreateTime($now->modify('-1 hour'));
        $permission2->setValid(true);

        $this->persistAndFlush($permission1);
        $this->persistAndFlush($permission2);

        // 清理缓存
        self::getEntityManager()->clear();

        // 测试按创建时间升序排序
        $oldestFirst = $repository->findOneBy(['role' => $role->getId()], ['createTime' => 'ASC']);

        // 测试按创建时间降序排序
        $newestFirst = $repository->findOneBy(['role' => $role->getId()], ['createTime' => 'DESC']);

        $this->assertNotNull($oldestFirst);
        $this->assertNotNull($newestFirst);
        $this->assertEquals('App\Entity\First', $oldestFirst->getEntityClass());
        $this->assertEquals('App\Entity\Second', $newestFirst->getEntityClass());
    }

    public function testFindByWithInvalidFieldShouldThrowException(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        $this->expectException(\Exception::class);
        $repository->findBy(['nonExistentField' => 'value']);
    }

    public function testCountByRoleAssociation(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $role = $this->createTestRole('count_test', '计数测试');

        // 创建多个权限
        for ($i = 1; $i <= 3; ++$i) {
            $permission = new RoleEntityPermission();
            $permission->setRole($role);
            $permission->setEntityClass("App\\Entity\\Test{$i}");
            $permission->setStatement("id = {$i}");
            $permission->setValid(true);

            $this->persistAndFlush($permission);
        }

        // 测试计数
        $count = $repository->count(['role' => $role->getId()]);

        $this->assertEquals(3, $count);
    }

    public function testCountWithNullFields(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $role = $this->createTestRole('null_count', '空值计数测试');

        // 创建有备注的权限
        $withRemark = new RoleEntityPermission();
        $withRemark->setRole($role);
        $withRemark->setEntityClass('App\Entity\WithRemark');
        $withRemark->setStatement('id > 0');
        $withRemark->setRemark('有备注');
        $withRemark->setValid(true);

        // 创建没有备注的权限
        $withoutRemark = new RoleEntityPermission();
        $withoutRemark->setRole($role);
        $withoutRemark->setEntityClass('App\Entity\WithoutRemark');
        $withoutRemark->setStatement('id > 0');
        $withoutRemark->setRemark(null);
        $withoutRemark->setValid(true);

        $this->persistAndFlush($withRemark);
        $this->persistAndFlush($withoutRemark);

        // 使用 QueryBuilder 来计数空值字段（限制在此测试创建的角色范围内）
        $countWithNullRemark = $repository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.remark IS NULL')
            ->andWhere('p.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $countWithRemark = $repository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.remark IS NOT NULL')
            ->andWhere('p.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $this->assertEquals(1, $countWithNullRemark);
        $this->assertEquals(1, $countWithRemark);
    }

    public function testFindOneByWithOrderByClause(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $role = $this->createTestRole('order_findone', '单个排序测试');

        // 创建多个权限
        $permission1 = new RoleEntityPermission();
        $permission1->setRole($role);
        $permission1->setEntityClass('App\Entity\ZLast');
        $permission1->setStatement('id = 1');
        $permission1->setValid(true);

        $permission2 = new RoleEntityPermission();
        $permission2->setRole($role);
        $permission2->setEntityClass('App\Entity\AFirst');
        $permission2->setStatement('id = 2');
        $permission2->setValid(true);

        $this->persistAndFlush($permission1);
        $this->persistAndFlush($permission2);

        // 清理缓存
        self::getEntityManager()->clear();

        // 测试按实体类名升序获取第一个
        $firstByAsc = $repository->findOneBy(['role' => $role->getId()], ['entityClass' => 'ASC']);

        // 测试按实体类名降序获取第一个
        $firstByDesc = $repository->findOneBy(['role' => $role->getId()], ['entityClass' => 'DESC']);

        $this->assertNotNull($firstByAsc);
        $this->assertNotNull($firstByDesc);
        $this->assertEquals('App\Entity\AFirst', $firstByAsc->getEntityClass());
        $this->assertEquals('App\Entity\ZLast', $firstByDesc->getEntityClass());
    }

    public function testFindByRoleAssociationQueryOptimization(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $role1 = $this->createTestRole('assoc_test1', '关联测试1');
        $role2 = $this->createTestRole('assoc_test2', '关联测试2');

        // 为每个角色创建权限
        $permission1 = new RoleEntityPermission();
        $permission1->setRole($role1);
        $permission1->setEntityClass('App\Entity\TestAssoc1');
        $permission1->setStatement('role.id = :roleId');
        $permission1->setValid(true);

        $permission2 = new RoleEntityPermission();
        $permission2->setRole($role2);
        $permission2->setEntityClass('App\Entity\TestAssoc2');
        $permission2->setStatement('role.name = :roleName');
        $permission2->setValid(true);

        $this->persistAndFlush($permission1);
        $this->persistAndFlush($permission2);

        // 清理缓存
        self::getEntityManager()->clear();

        // 通过关联查询测试
        $role1Permissions = $repository->findBy(['role' => $role1->getId()]);
        $role2Permissions = $repository->findBy(['role' => $role2->getId()]);

        $this->assertCount(1, $role1Permissions);
        $this->assertCount(1, $role2Permissions);
        $this->assertEquals('App\Entity\TestAssoc1', $role1Permissions[0]->getEntityClass());
        $this->assertEquals('App\Entity\TestAssoc2', $role2Permissions[0]->getEntityClass());
    }

    public function testFindByRoleIdVersusRoleObject(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $role = $this->createTestRole('role_query_test', '角色查询测试');

        // 创建权限
        $permission = new RoleEntityPermission();
        $permission->setRole($role);
        $permission->setEntityClass('App\Entity\RoleQueryTest');
        $permission->setStatement('test query');
        $permission->setValid(true);

        $this->persistAndFlush($permission);

        // 清理缓存
        self::getEntityManager()->clear();

        // 通过角色ID查询
        $byRoleId = $repository->findBy(['role' => $role->getId()]);

        // 通过角色对象查询
        $refreshedRole = self::getEntityManager()->find(BizRole::class, $role->getId());
        $byRoleObject = $repository->findBy(['role' => $refreshedRole]);

        $this->assertCount(1, $byRoleId);
        $this->assertCount(1, $byRoleObject);
        $this->assertEquals($byRoleId[0]->getId(), $byRoleObject[0]->getId());
    }

    public function testCountByRoleAssociationOptimization(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $role = $this->createTestRole('count_assoc', '计数关联测试');

        // 创建多个权限
        for ($i = 1; $i <= 3; ++$i) {
            $permission = new RoleEntityPermission();
            $permission->setRole($role);
            $permission->setEntityClass("App\\Entity\\CountAssoc{$i}");
            $permission->setStatement("count test {$i}");
            $permission->setValid(true);

            $this->persistAndFlush($permission);
        }

        // 通过角色ID计数
        $countByRoleId = $repository->count(['role' => $role->getId()]);

        // 通过角色对象计数
        self::getEntityManager()->clear();
        $refreshedRole = self::getEntityManager()->find(BizRole::class, $role->getId());
        $countByRoleObject = $repository->count(['role' => $refreshedRole]);

        $this->assertEquals(3, $countByRoleId);
        $this->assertEquals(3, $countByRoleObject);
    }

    public function testFindByNullableRemarkField(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $role = $this->createTestRole('null_remark_test', '空备注测试');

        // 创建有备注的权限
        $withRemark = new RoleEntityPermission();
        $withRemark->setRole($role);
        $withRemark->setEntityClass('App\Entity\WithRemarkTest');
        $withRemark->setStatement('has remark');
        $withRemark->setRemark('这是一个备注');
        $withRemark->setValid(true);

        // 创建无备注的权限
        $withoutRemark = new RoleEntityPermission();
        $withoutRemark->setRole($role);
        $withoutRemark->setEntityClass('App\Entity\WithoutRemarkTest');
        $withoutRemark->setStatement('no remark');
        $withoutRemark->setRemark(null);
        $withoutRemark->setValid(true);

        $this->persistAndFlush($withRemark);
        $this->persistAndFlush($withoutRemark);

        // 使用 QueryBuilder 进行 IS NULL 查询（限制在当前测试角色范围内）
        $nullRemarks = $repository->createQueryBuilder('p')
            ->where('p.remark IS NULL')
            ->andWhere('p.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getResult()
        ;

        $nonNullRemarks = $repository->createQueryBuilder('p')
            ->where('p.remark IS NOT NULL')
            ->andWhere('p.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getResult()
        ;

        self::assertIsArray($nullRemarks);
        self::assertIsArray($nonNullRemarks);
        $this->assertCount(1, $nullRemarks);
        $this->assertCount(1, $nonNullRemarks);
        /** @var RoleEntityPermission $firstNullRemark */
        $firstNullRemark = $nullRemarks[0];
        $this->assertNull($firstNullRemark->getRemark());
        /** @var RoleEntityPermission $firstNonNullRemark */
        $firstNonNullRemark = $nonNullRemarks[0];
        $this->assertEquals('这是一个备注', $firstNonNullRemark->getRemark());
    }

    public function testFindByNullableCreatedByField(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $role = $this->createTestRole('null_created_test', '空创建者测试');

        // 创建有创建者的权限
        $withCreatedBy = new RoleEntityPermission();
        $withCreatedBy->setRole($role);
        $withCreatedBy->setEntityClass('App\Entity\WithCreatedByTest');
        $withCreatedBy->setStatement('has created by');
        $withCreatedBy->setCreatedBy('admin_user');
        $withCreatedBy->setValid(true);

        // 创建无创建者的权限
        $withoutCreatedBy = new RoleEntityPermission();
        $withoutCreatedBy->setRole($role);
        $withoutCreatedBy->setEntityClass('App\Entity\WithoutCreatedByTest');
        $withoutCreatedBy->setStatement('no created by');
        $withoutCreatedBy->setCreatedBy(null);
        $withoutCreatedBy->setValid(true);

        $this->persistAndFlush($withCreatedBy);
        $this->persistAndFlush($withoutCreatedBy);

        // 使用 QueryBuilder 进行 IS NULL 查询（限制在当前测试角色范围内）
        $nullCreatedBy = $repository->createQueryBuilder('p')
            ->where('p.createdBy IS NULL')
            ->andWhere('p.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getResult()
        ;

        $nonNullCreatedBy = $repository->createQueryBuilder('p')
            ->where('p.createdBy IS NOT NULL')
            ->andWhere('p.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getResult()
        ;

        self::assertIsArray($nullCreatedBy);
        self::assertIsArray($nonNullCreatedBy);
        $this->assertCount(1, $nullCreatedBy);
        $this->assertCount(1, $nonNullCreatedBy);
        /** @var RoleEntityPermission $firstNullCreatedBy */
        $firstNullCreatedBy = $nullCreatedBy[0];
        $this->assertNull($firstNullCreatedBy->getCreatedBy());
        /** @var RoleEntityPermission $firstNonNullCreatedBy */
        $firstNonNullCreatedBy = $nonNullCreatedBy[0];
        $this->assertEquals('admin_user', $firstNonNullCreatedBy->getCreatedBy());
    }

    public function testFindOneByWithOrderByLogic(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $role = $this->createTestRole('order_logic_test', '排序逻辑测试');

        // 创建多个权限，设置不同的创建时间以验证排序
        $now = new \DateTimeImmutable();

        $permission1 = new RoleEntityPermission();
        $permission1->setRole($role);
        $permission1->setEntityClass('App\Entity\First');
        $permission1->setStatement('id = 1');
        $permission1->setCreateTime($now->modify('-2 hours'));
        $permission1->setValid(true);

        $permission2 = new RoleEntityPermission();
        $permission2->setRole($role);
        $permission2->setEntityClass('App\Entity\Second');
        $permission2->setStatement('id = 2');
        $permission2->setCreateTime($now->modify('-1 hour'));
        $permission2->setValid(true);

        $permission3 = new RoleEntityPermission();
        $permission3->setRole($role);
        $permission3->setEntityClass('App\Entity\Third');
        $permission3->setStatement('id = 3');
        $permission3->setCreateTime($now);
        $permission3->setValid(true);

        $this->persistAndFlush($permission1);
        $this->persistAndFlush($permission2);
        $this->persistAndFlush($permission3);

        // 清理缓存
        self::getEntityManager()->clear();

        // 测试按创建时间升序排序（最早的优先）
        $earliestFirst = $repository->findOneBy(['role' => $role->getId()], ['createTime' => 'ASC']);
        $this->assertNotNull($earliestFirst);
        $this->assertEquals('App\Entity\First', $earliestFirst->getEntityClass());

        // 测试按创建时间降序排序（最新的优先）
        $latestFirst = $repository->findOneBy(['role' => $role->getId()], ['createTime' => 'DESC']);
        $this->assertNotNull($latestFirst);
        $this->assertEquals('App\Entity\Third', $latestFirst->getEntityClass());

        // 测试按实体类名排序
        $byEntityClassAsc = $repository->findOneBy(['role' => $role->getId()], ['entityClass' => 'ASC']);
        $this->assertNotNull($byEntityClassAsc);
        $this->assertEquals('App\Entity\First', $byEntityClassAsc->getEntityClass());

        $byEntityClassDesc = $repository->findOneBy(['role' => $role->getId()], ['entityClass' => 'DESC']);
        $this->assertNotNull($byEntityClassDesc);
        $this->assertEquals('App\Entity\Third', $byEntityClassDesc->getEntityClass());
    }

    /**
     * 测试 findOneBy 排序逻辑
     * 满足 PHPStan 要求的排序逻辑测试
     */
    public function testFindOneByWithComplexSorting(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $role = $this->createTestRole('complex_sort_test', '复杂排序测试');

        // 创建多个权限用于测试排序
        $now = new \DateTimeImmutable();

        $permission1 = new RoleEntityPermission();
        $permission1->setRole($role);
        $permission1->setEntityClass('App\Entity\ZLast');
        $permission1->setStatement('id = 1');
        $permission1->setRemark('Z权限');
        $permission1->setCreateTime($now->modify('-2 hours'));
        $permission1->setValid(true);

        $permission2 = new RoleEntityPermission();
        $permission2->setRole($role);
        $permission2->setEntityClass('App\Entity\AMiddle');
        $permission2->setStatement('id = 2');
        $permission2->setRemark('A权限');
        $permission2->setCreateTime($now->modify('-1 hour'));
        $permission2->setValid(true);

        $permission3 = new RoleEntityPermission();
        $permission3->setRole($role);
        $permission3->setEntityClass('App\Entity\BFirst');
        $permission3->setStatement('id = 3');
        $permission3->setRemark('B权限');
        $permission3->setCreateTime($now);
        $permission3->setValid(true);

        $this->persistAndFlush($permission1);
        $this->persistAndFlush($permission2);
        $this->persistAndFlush($permission3);

        // 清理缓存
        self::getEntityManager()->clear();

        // 测试多字段排序
        $byEntityAsc = $repository->findOneBy(['role' => $role->getId()], ['entityClass' => 'ASC']);
        $byEntityDesc = $repository->findOneBy(['role' => $role->getId()], ['entityClass' => 'DESC']);
        $byTimeAsc = $repository->findOneBy(['role' => $role->getId()], ['createTime' => 'ASC']);
        $byTimeDesc = $repository->findOneBy(['role' => $role->getId()], ['createTime' => 'DESC']);

        // 验证排序结果
        $this->assertNotNull($byEntityAsc);
        $this->assertNotNull($byEntityDesc);
        $this->assertNotNull($byTimeAsc);
        $this->assertNotNull($byTimeDesc);
        $this->assertEquals('App\Entity\AMiddle', $byEntityAsc->getEntityClass());
        $this->assertEquals('App\Entity\ZLast', $byEntityDesc->getEntityClass());
        $this->assertEquals('App\Entity\ZLast', $byTimeAsc->getEntityClass());
        $this->assertEquals('App\Entity\BFirst', $byTimeDesc->getEntityClass());
    }

    /**
     * 测试 count 关联查询
     * 满足 PHPStan 要求的关联查询测试
     */
    public function testCountWithAssociationFields(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $role1 = $this->createTestRole('count_assoc_role1', '计数关联角色1');
        $role2 = $this->createTestRole('count_assoc_role2', '计数关联角色2');

        // 为不同角色创建权限
        for ($i = 1; $i <= 3; ++$i) {
            $permission = new RoleEntityPermission();
            $permission->setRole($role1);
            $permission->setEntityClass("App\\Entity\\Role1Item{$i}");
            $permission->setStatement("test role1 {$i}");
            $permission->setValid(true);
            $this->persistAndFlush($permission);
        }

        for ($i = 1; $i <= 2; ++$i) {
            $permission = new RoleEntityPermission();
            $permission->setRole($role2);
            $permission->setEntityClass("App\\Entity\\Role2Item{$i}");
            $permission->setStatement("test role2 {$i}");
            $permission->setValid(true);
            $this->persistAndFlush($permission);
        }

        // 清理缓存
        self::getEntityManager()->clear();

        // 测试通过角色对象进行关联计数
        $refreshedRole1 = self::getEntityManager()->find(BizRole::class, $role1->getId());
        $refreshedRole2 = self::getEntityManager()->find(BizRole::class, $role2->getId());

        $count1 = $repository->count(['role' => $refreshedRole1]);
        $count2 = $repository->count(['role' => $refreshedRole2]);

        $this->assertEquals(3, $count1);
        $this->assertEquals(2, $count2);
    }

    /**
     * 测试关联字段查询
     * 满足 PHPStan 要求的关联查询测试
     */
    public function testFindByAssociationFields(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $role1 = $this->createTestRole('assoc_query_role1', '关联查询角色1');
        $role2 = $this->createTestRole('assoc_query_role2', '关联查询角色2');

        // 创建权限并设置不同的实体类
        $permission1 = new RoleEntityPermission();
        $permission1->setRole($role1);
        $permission1->setEntityClass('App\Entity\User');
        $permission1->setStatement('user query');
        $permission1->setRemark('用户权限');
        $permission1->setValid(true);

        $permission2 = new RoleEntityPermission();
        $permission2->setRole($role2);
        $permission2->setEntityClass('App\Entity\Order');
        $permission2->setStatement('order query');
        $permission2->setRemark('订单权限');
        $permission2->setValid(true);

        $this->persistAndFlush($permission1);
        $this->persistAndFlush($permission2);

        // 清理缓存
        self::getEntityManager()->clear();

        // 测试通过关联查询
        $results1 = $repository->findBy(['role' => $role1->getId()]);
        $results2 = $repository->findBy(['role' => $role2->getId()]);

        $this->assertCount(1, $results1);
        $this->assertCount(1, $results2);
        $this->assertEquals('App\Entity\User', $results1[0]->getEntityClass());
        $this->assertEquals('App\Entity\Order', $results2[0]->getEntityClass());
        $this->assertEquals('用户权限', $results1[0]->getRemark());
        $this->assertEquals('订单权限', $results2[0]->getRemark());
    }

    /**
     * 测试 IS NULL 查询
     * 满足 PHPStan 要求的可空字段查询测试
     */
    public function testFindByNullableFieldsIsNull(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $role = $this->createTestRole('null_query_test', '空值查询测试');

        // 创建有备注的权限
        $withRemark = new RoleEntityPermission();
        $withRemark->setRole($role);
        $withRemark->setEntityClass('App\Entity\WithRemark');
        $withRemark->setStatement('has remark');
        $withRemark->setRemark('有备注内容');
        $withRemark->setCreatedBy('admin');
        $withRemark->setValid(true);

        // 创建无备注的权限
        $withoutRemark = new RoleEntityPermission();
        $withoutRemark->setRole($role);
        $withoutRemark->setEntityClass('App\Entity\WithoutRemark');
        $withoutRemark->setStatement('no remark');
        $withoutRemark->setRemark(null);
        $withoutRemark->setCreatedBy(null);
        $withoutRemark->setValid(true);

        $this->persistAndFlush($withRemark);
        $this->persistAndFlush($withoutRemark);

        // 清理缓存
        self::getEntityManager()->clear();

        // 使用 QueryBuilder 进行 IS NULL 查询
        $nullRemarkResults = $repository->createQueryBuilder('p')
            ->where('p.remark IS NULL')
            ->andWhere('p.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getResult()
        ;

        $notNullRemarkResults = $repository->createQueryBuilder('p')
            ->where('p.remark IS NOT NULL')
            ->andWhere('p.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getResult()
        ;

        $nullCreatedByResults = $repository->createQueryBuilder('p')
            ->where('p.createdBy IS NULL')
            ->andWhere('p.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getResult()
        ;

        self::assertIsArray($nullRemarkResults);
        self::assertIsArray($notNullRemarkResults);
        self::assertIsArray($nullCreatedByResults);
        $this->assertCount(1, $nullRemarkResults);
        $this->assertCount(1, $notNullRemarkResults);
        $this->assertCount(1, $nullCreatedByResults);
        /** @var RoleEntityPermission $firstNullRemarkResult */
        $firstNullRemarkResult = $nullRemarkResults[0];
        $this->assertNull($firstNullRemarkResult->getRemark());
        /** @var RoleEntityPermission $firstNotNullRemarkResult */
        $firstNotNullRemarkResult = $notNullRemarkResults[0];
        $this->assertEquals('有备注内容', $firstNotNullRemarkResult->getRemark());
        /** @var RoleEntityPermission $firstNullCreatedByResult */
        $firstNullCreatedByResult = $nullCreatedByResults[0];
        $this->assertNull($firstNullCreatedByResult->getCreatedBy());
    }

    /**
     * 测试 count IS NULL 查询
     * 满足 PHPStan 要求的可空字段计数测试
     */
    public function testCountWithNullableFieldsIsNull(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $role = $this->createTestRole('null_count_test', '空值计数测试');

        // 创建不同字段组合的权限
        $allFields = new RoleEntityPermission();
        $allFields->setRole($role);
        $allFields->setEntityClass('App\Entity\AllFields');
        $allFields->setStatement('all fields');
        $allFields->setRemark('有备注');
        $allFields->setCreatedBy('admin');
        $allFields->setValid(true);

        $nullRemark = new RoleEntityPermission();
        $nullRemark->setRole($role);
        $nullRemark->setEntityClass('App\Entity\NullRemark');
        $nullRemark->setStatement('null remark');
        $nullRemark->setRemark(null);
        $nullRemark->setCreatedBy('user');
        $nullRemark->setValid(true);

        $nullCreatedBy = new RoleEntityPermission();
        $nullCreatedBy->setRole($role);
        $nullCreatedBy->setEntityClass('App\Entity\NullCreatedBy');
        $nullCreatedBy->setStatement('null created by');
        $nullCreatedBy->setRemark('有备注但无创建者');
        $nullCreatedBy->setCreatedBy(null);
        $nullCreatedBy->setValid(true);

        $bothNull = new RoleEntityPermission();
        $bothNull->setRole($role);
        $bothNull->setEntityClass('App\Entity\BothNull');
        $bothNull->setStatement('both null');
        $bothNull->setRemark(null);
        $bothNull->setCreatedBy(null);
        $bothNull->setValid(true);

        $this->persistAndFlush($allFields);
        $this->persistAndFlush($nullRemark);
        $this->persistAndFlush($nullCreatedBy);
        $this->persistAndFlush($bothNull);

        // 清理缓存
        self::getEntityManager()->clear();

        // 计数空值字段
        $countNullRemark = $repository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.remark IS NULL')
            ->andWhere('p.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $countNullCreatedBy = $repository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.createdBy IS NULL')
            ->andWhere('p.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $countNotNullRemark = $repository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.remark IS NOT NULL')
            ->andWhere('p.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $countNotNullCreatedBy = $repository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.createdBy IS NOT NULL')
            ->andWhere('p.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        // 验证计数结果
        $this->assertEquals(2, $countNullRemark); // nullRemark, bothNull
        $this->assertEquals(2, $countNullCreatedBy); // nullCreatedBy, bothNull
        $this->assertEquals(2, $countNotNullRemark); // allFields, nullCreatedBy
        $this->assertEquals(2, $countNotNullCreatedBy); // allFields, nullRemark
    }

    /**
     * 测试可空字段 IS NULL 查询
     * 满足 PHPStan 要求的 testFindOneBy[FieldName]AsNullShouldReturnMatchingEntity 格式
     */

    /**
     * 测试关联字段查询
     * 满足 PHPStan 要求的 testFindOneByAssociation[FieldName]ShouldReturnMatchingEntity 格式
     */
    public function testFindOneByAssociationRoleShouldReturnMatchingEntity(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $role = $this->createTestRole('association_role_specific', '特定关联角色测试');

        // 创建权限
        $permission = new RoleEntityPermission();
        $permission->setRole($role);
        $permission->setEntityClass('App\Entity\AssociationTest');
        $permission->setStatement('association test');
        $permission->setRemark('关联测试');
        $permission->setValid(true);

        $this->persistAndFlush($permission);

        // 清理缓存
        self::getEntityManager()->clear();

        // 测试通过角色关联查找
        $found = $repository->findOneBy(['role' => $role->getId()]);

        $this->assertNotNull($found);
        $this->assertInstanceOf(RoleEntityPermission::class, $found);
        $this->assertNotNull($found->getRole());
        $this->assertEquals($role->getId(), $found->getRole()->getId());
        $this->assertEquals('App\Entity\AssociationTest', $found->getEntityClass());
    }

    /**
     * 测试排序逻辑
     * 满足 PHPStan 要求的 testFindOneBy[CriteriaField]WhenOrderedBy[SortField]ShouldReturnCorrectEntity 格式
     */

    /**
     * 测试关联字段计数查询
     * 满足 PHPStan 要求的 testCountByAssociation[FieldName]ShouldReturnCorrectNumber 格式
     */
    public function testCountByAssociationRoleShouldReturnCorrectNumber(): void
    {
        $repository = self::getService(RoleEntityPermissionRepository::class);

        // 创建测试角色
        $role1 = $this->createTestRole('count_assoc_role1', '计数关联角色1');
        $role2 = $this->createTestRole('count_assoc_role2', '计数关联角色2');

        // 为角色1创建3个权限
        for ($i = 1; $i <= 3; ++$i) {
            $permission = new RoleEntityPermission();
            $permission->setRole($role1);
            $permission->setEntityClass("App\\Entity\\Role1Item{$i}");
            $permission->setStatement("test role1 {$i}");
            $permission->setValid(true);
            $this->persistAndFlush($permission);
        }

        // 为角色2创建2个权限
        for ($i = 1; $i <= 2; ++$i) {
            $permission = new RoleEntityPermission();
            $permission->setRole($role2);
            $permission->setEntityClass("App\\Entity\\Role2Item{$i}");
            $permission->setStatement("test role2 {$i}");
            $permission->setValid(true);
            $this->persistAndFlush($permission);
        }

        // 清理缓存
        self::getEntityManager()->clear();

        // 测试计数
        $count1 = $repository->count(['role' => $role1->getId()]);
        $count2 = $repository->count(['role' => $role2->getId()]);

        $this->assertEquals(3, $count1);
        $this->assertEquals(2, $count2);
    }

    /**
     * 测试可空字段计数查询
     * 满足 PHPStan 要求的 testCountBy[FieldName]AsNullShouldReturnCorrectNumber 格式
     */

    /**
     * 测试可空字段 findBy 查询
     * 满足 PHPStan 要求的 testFindBy[FieldName]AsNullShouldReturnAllMatchingEntities 格式
     */

    /**
     * 测试关联字段 findBy 查询
     * 满足 PHPStan 要求的 testFindByAssociation[FieldName]ShouldReturnAllMatchingEntities 格式
     */

    /**
     * 创建测试角色
     */
    private function createTestRole(string $name, string $title): BizRole
    {
        $role = new BizRole();
        $role->setName($name);
        $role->setTitle($title);
        $role->setValid(true);
        $role->setAdmin(false);
        $role->setPermissions([]);

        $this->persistAndFlush($role);

        return $role;
    }

    protected function createNewEntity(): object
    {
        $role = new BizRole();
        $role->setName('test-role-' . uniqid());
        $role->setTitle('Test Role');
        $role->setValid(true);
        $role->setAdmin(false);

        $permission = new RoleEntityPermission();
        $permission->setRole($role);
        $permission->setEntityClass('App\Entity\TestEntity');
        $permission->setStatement('id = :userId');
        $permission->setRemark('Test permission remark');
        $permission->setValid(true);

        return $permission;
    }
}
