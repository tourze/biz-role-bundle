<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\BizRoleBundle\Entity\BizRole;
use Tourze\BizRoleBundle\Repository\BizRoleRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(BizRoleRepository::class)]
#[RunTestsInSeparateProcesses]
final class BizRoleRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // Repository测试的初始化逻辑（如有需要）
    }

    protected function getRepository(): BizRoleRepository
    {
        return self::getService(BizRoleRepository::class);
    }

    public function testRepositoryCanHandleBizRoleEntity(): void
    {
        $role = new BizRole();
        $role->setName('测试角色');
        $role->setTitle('测试角色标题');

        $this->assertEquals('测试角色', $role->getName());
        $this->assertEquals('测试角色标题', $role->getTitle());
    }

    public function testSave(): void
    {
        $repository = self::getService(BizRoleRepository::class);

        $role = new BizRole();
        $role->setName('new-role');
        $role->setTitle('新角色');
        $role->setAdmin(true);
        $role->setValid(true);
        $role->setPermissions(['user.view', 'user.edit']);

        $repository->save($role);

        // 验证已保存到数据库
        $this->assertEntityPersisted($role);
        $this->assertGreaterThan(0, $role->getId());
    }

    public function testSaveWithFlushFalse(): void
    {
        $repository = self::getService(BizRoleRepository::class);
        $em = self::getEntityManager();

        $role = new BizRole();
        $role->setName('new-role-no-flush');
        $role->setTitle('新角色不刷新');

        $repository->save($role, false);

        // 验证实体在管理状态但未刷新到数据库
        $this->assertTrue($em->contains($role));
        $this->assertEquals(0, $role->getId()); // ID还未生成

        // 手动刷新
        $em->flush();
        $this->assertGreaterThan(0, $role->getId());
    }

    public function testRemove(): void
    {
        $repository = self::getService(BizRoleRepository::class);

        // 创建并保存实体
        $role = new BizRole();
        $role->setName('to-be-removed');
        $role->setTitle('待删除角色');
        $this->persistAndFlush($role);
        $roleId = $role->getId();

        // 验证实体存在
        $this->assertGreaterThan(0, $roleId);

        // 删除实体
        $repository->remove($role);

        // 验证已从数据库删除
        $this->assertEntityNotExists(BizRole::class, $roleId);
    }

    public function testRemoveWithFlushFalse(): void
    {
        $repository = self::getService(BizRoleRepository::class);
        $em = self::getEntityManager();

        // 创建并保存实体
        $role = new BizRole();
        $role->setName('to-be-removed-no-flush');
        $role->setTitle('待删除角色不刷新');
        $this->persistAndFlush($role);
        $roleId = $role->getId();

        // 删除实体但不刷新
        $repository->remove($role, false);

        // 验证实体仍存在于数据库
        $found = $em->find(BizRole::class, $roleId);
        $this->assertNotNull($found);

        // 手动刷新
        $em->flush();
        $this->assertEntityNotExists(BizRole::class, $roleId);
    }

    public function testRepositoryCanWorkWithBizRoleProperties(): void
    {
        $role = new BizRole();

        $role->setName('管理员');
        $role->setTitle('系统管理员');
        $role->setAdmin(true);
        $role->setValid(true);

        $this->assertEquals('管理员', $role->getName());
        $this->assertEquals('系统管理员', $role->getTitle());
        $this->assertTrue($role->isAdmin());
        $this->assertTrue($role->isValid());
    }

    public function testBizRoleStringRepresentation(): void
    {
        $role = new BizRole();
        $role->setName('管理员');
        $role->setTitle('系统管理员');

        // 初始状态没有ID时返回空字符串
        $this->assertEquals('', (string) $role);
    }

    public function testBizRolePermissions(): void
    {
        $role = new BizRole();
        $permissions = ['user.view', 'user.edit'];

        $role->setPermissions($permissions);
        $this->assertEquals($permissions, $role->getPermissions());
    }

    public function testLoadUserByName(): void
    {
        $repository = self::getService(BizRoleRepository::class);

        // 测试加载不存在的角色
        $result = $repository->loadUserByName('nonexistent_role');
        $this->assertNull($result);
    }

    public function testLoadValidRoles(): void
    {
        $repository = self::getService(BizRoleRepository::class);

        // 先清空数据库确保测试隔离
        $em = self::getEntityManager();
        $em->createQuery('DELETE FROM ' . BizRole::class)->execute();

        // 创建有效和无效角色
        $validRole = new BizRole();
        $validRole->setName('valid-role');
        $validRole->setTitle('有效角色');
        $validRole->setValid(true);
        $this->persistAndFlush($validRole);

        $invalidRole = new BizRole();
        $invalidRole->setName('invalid-role');
        $invalidRole->setTitle('无效角色');
        $invalidRole->setValid(false);
        $this->persistAndFlush($invalidRole);

        $roles = $repository->loadValidRoles();

        $this->assertIsArray($roles);
        $this->assertCount(1, $roles);
        $this->assertEquals('valid-role', $roles[0]->getName());
    }

    public function testFindByWithAssociatedUsers(): void
    {
        $repository = self::getService(BizRoleRepository::class);

        // 先清空数据库确保测试隔离
        $em = self::getEntityManager();
        $em->createQuery('DELETE FROM ' . BizRole::class)->execute();

        // 创建角色
        $role = new BizRole();
        $role->setName('admin-role');
        $role->setTitle('管理员角色');
        $role->setAdmin(true);
        $this->persistAndFlush($role);

        // 测试按admin字段查询
        $adminRoles = $repository->findBy(['admin' => true]);

        $this->assertIsArray($adminRoles);
        $this->assertCount(1, $adminRoles);
        $this->assertTrue($adminRoles[0]->isAdmin());
    }

    public function testFindByWithNullFields(): void
    {
        $repository = self::getService(BizRoleRepository::class);

        // 先清空数据库确保测试隔离
        $em = self::getEntityManager();
        $em->createQuery('DELETE FROM ' . BizRole::class)->execute();

        // 创建角色，admin字段为null
        $role = new BizRole();
        $role->setName('null-admin-role');
        $role->setTitle('空管理员角色');
        $role->setAdmin(null);
        $this->persistAndFlush($role);

        // 查询admin为null的角色
        $nullAdminRoles = $repository->findBy(['admin' => null]);

        $this->assertIsArray($nullAdminRoles);
        $this->assertCount(1, $nullAdminRoles);
        $this->assertNull($nullAdminRoles[0]->isAdmin());
    }

    public function testFindOneByWithOrderBy(): void
    {
        $repository = self::getService(BizRoleRepository::class);

        // 先清空数据库确保测试隔离
        $em = self::getEntityManager();
        $em->createQuery('DELETE FROM ' . BizRole::class)->execute();

        // 创建多个角色
        $role1 = new BizRole();
        $role1->setName('b-role');
        $role1->setTitle('B角色');
        $role1->setValid(true);
        $this->persistAndFlush($role1);

        $role2 = new BizRole();
        $role2->setName('a-role');
        $role2->setTitle('A角色');
        $role2->setValid(true);
        $this->persistAndFlush($role2);

        // 按名称排序查找第一个有效角色
        $result = $repository->findOneBy(
            ['valid' => true],
            ['name' => 'ASC']
        );

        $this->assertNotNull($result);
        $this->assertEquals('a-role', $result->getName());
    }

    public function testFindByWithInvalidCriteria(): void
    {
        $repository = self::getService(BizRoleRepository::class);

        // 使用不存在的字段进行查询应该抛出异常
        $this->expectException(\Exception::class);
        $repository->findBy(['nonExistentField' => 'value']);
    }

    public function testFindOrCreate(): void
    {
        $repository = self::getService(BizRoleRepository::class);

        // 先清空数据库确保测试隔离
        $em = self::getEntityManager();
        $em->createQuery('DELETE FROM ' . BizRole::class)->execute();

        // 测试创建新角色
        $result = $repository->findOrCreate('new-test-role', '新测试角色');

        $this->assertNotNull($result);
        $this->assertInstanceOf(BizRole::class, $result);
        $this->assertEquals('new-test-role', $result->getName());
        $this->assertEquals('新测试角色', $result->getTitle());
        $this->assertGreaterThan(0, $result->getId());

        // 测试查找已存在的角色
        $existingResult = $repository->findOrCreate('new-test-role', '另一个标题');

        $this->assertEquals($result->getId(), $existingResult->getId());
        $this->assertEquals('新测试角色', $existingResult->getTitle()); // 应该保持原标题
    }

    public function testFindOrCreateWithNullTitle(): void
    {
        $repository = self::getService(BizRoleRepository::class);

        // 先清空数据库确保测试隔离
        $em = self::getEntityManager();
        $em->createQuery('DELETE FROM ' . BizRole::class)->execute();

        $result = $repository->findOrCreate('role-without-title');

        $this->assertNotNull($result);
        $this->assertEquals('role-without-title', $result->getName());
        $this->assertEquals('role-without-title', $result->getTitle()); // 标题应该使用名称
    }

    public function testLoadUserByNameWithExistingName(): void
    {
        $repository = self::getService(BizRoleRepository::class);

        // 先清空数据库确保测试隔离
        $em = self::getEntityManager();
        $em->createQuery('DELETE FROM ' . BizRole::class)->execute();

        // 创建角色
        $role = new BizRole();
        $role->setName('existing-role');
        $role->setTitle('存在的角色');
        $this->persistAndFlush($role);

        $result = $repository->loadUserByName('existing-role');

        $this->assertNotNull($result);
        $this->assertInstanceOf(BizRole::class, $result);
        $this->assertEquals('existing-role', $result->getName());
    }

    public function testComplexQueryWithMultipleCriteria(): void
    {
        $repository = self::getService(BizRoleRepository::class);

        // 先清空数据库确保测试隔离
        $em = self::getEntityManager();
        $em->createQuery('DELETE FROM ' . BizRole::class)->execute();

        // 创建多个角色用于复杂查询测试
        $adminRole = new BizRole();
        $adminRole->setName('admin');
        $adminRole->setTitle('管理员');
        $adminRole->setAdmin(true);
        $adminRole->setValid(true);
        $this->persistAndFlush($adminRole);

        $userRole = new BizRole();
        $userRole->setName('user');
        $userRole->setTitle('用户');
        $userRole->setAdmin(false);
        $userRole->setValid(true);
        $this->persistAndFlush($userRole);

        $invalidRole = new BizRole();
        $invalidRole->setName('invalid');
        $invalidRole->setTitle('无效角色');
        $invalidRole->setAdmin(true);
        $invalidRole->setValid(false);
        $this->persistAndFlush($invalidRole);

        // 查询有效的管理员角色
        $validAdminRoles = $repository->findBy([
            'admin' => true,
            'valid' => true,
        ]);

        $this->assertIsArray($validAdminRoles);
        $this->assertCount(1, $validAdminRoles);
        $this->assertEquals('admin', $validAdminRoles[0]->getName());
    }

    protected function createNewEntity(): object
    {
        $role = new BizRole();
        $role->setName('test-role-' . uniqid());
        $role->setTitle('Test Role Title');
        $role->setAdmin(false);
        $role->setValid(true);
        $role->setPermissions(['user.view', 'user.edit']);
        $role->setMenuJson('{"menu": []}');
        $role->setExcludePermissions(['user.delete']);
        $role->setHierarchicalRoles(['ROLE_USER']);

        return $role;
    }
}
