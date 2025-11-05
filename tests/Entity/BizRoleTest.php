<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\BizRoleBundle\Entity\BizRole;
use Tourze\BizRoleBundle\Entity\RoleEntityPermission;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(BizRole::class)]
final class BizRoleTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new BizRole();
    }

    private BizRole $role;

    protected function setUp(): void
    {
        parent::setUp();
        $this->role = new BizRole();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'name' => ['name', 'ROLE_ADMIN'],
            'title' => ['title', '系统管理员'],
            'admin' => ['admin', true],
            'permissions' => ['permissions', ['user_manage', 'role_manage']],
            'valid' => ['valid', true],
            'menuJson' => ['menuJson', '{"menu": "data"}'],
            'excludePermissions' => ['excludePermissions', ['exclude_perm']],
            'hierarchicalRoles' => ['hierarchicalRoles', ['ROLE_USER']],
        ];
    }

    /**
     * 测试构造函数初始化集合属性
     */
    public function testConstructor(): void
    {
        // 测试 users 集合初始化
        $this->assertInstanceOf(ArrayCollection::class, $this->getObjectProperty($this->role, 'users'));
        $this->assertEmpty($this->getObjectProperty($this->role, 'users'));

        // 测试 dataPermissions 集合初始化
        $this->assertInstanceOf(ArrayCollection::class, $this->getObjectProperty($this->role, 'dataPermissions'));
        $this->assertEmpty($this->getObjectProperty($this->role, 'dataPermissions'));
    }

    /**
     * 测试对象字符串表示
     */
    public function testToString(): void
    {
        // 测试没有 ID 的情况
        $this->assertEquals('', (string) $this->role);

        // 设置 ID 和必要属性
        $reflection = new \ReflectionClass($this->role);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->role, 1);

        $this->role->setName('ROLE_ADMIN');
        $this->role->setTitle('系统管理员');

        $expected = '系统管理员(ROLE_ADMIN)';
        $this->assertEquals($expected, (string) $this->role);
    }

    /**
     * 测试获取用户集合（基础功能）
     */
    public function testGetUsers(): void
    {
        $users = $this->role->getUsers();

        $this->assertInstanceOf(ArrayCollection::class, $users);
        $this->assertCount(0, $users);
    }

    /**
     * 测试添加数据权限
     */
    public function testAddDataPermission(): void
    {
        $permission = new RoleEntityPermission();
        $permission->setEntityClass('TestEntity');

        $this->role->addDataPermission($permission);

        // 检查权限是否已添加
        $permissions = $this->getObjectProperty($this->role, 'dataPermissions');
        self::assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $permissions);
        $this->assertCount(1, $permissions);
        $this->assertSame($permission, $permissions->first());

        // 检查双向关系是否建立
        $this->assertSame($this->role, $permission->getRole());
    }

    /**
     * 测试移除数据权限
     */
    public function testRemoveDataPermission(): void
    {
        // 先添加权限
        $permission = new RoleEntityPermission();
        $permission->setEntityClass('TestEntity');
        $this->role->addDataPermission($permission);

        // 检查权限是否已添加
        $permissions = $this->getObjectProperty($this->role, 'dataPermissions');
        self::assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $permissions);
        $this->assertCount(1, $permissions);

        // 移除权限
        $this->role->removeDataPermission($permission);

        // 检查权限是否已移除
        $permissions = $this->getObjectProperty($this->role, 'dataPermissions');
        self::assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $permissions);
        $this->assertCount(0, $permissions);

        // 检查双向关系是否解除
        $this->assertNull($permission->getRole());
    }

    /**
     * 测试获取数据权限集合
     */
    public function testGetDataPermissions(): void
    {
        $permissions = $this->role->getDataPermissions();

        $this->assertInstanceOf(ArrayCollection::class, $permissions);
        $this->assertCount(0, $permissions);

        // 添加权限后再测试
        $permission = new RoleEntityPermission();
        $this->role->addDataPermission($permission);

        $permissions = $this->role->getDataPermissions();
        $this->assertCount(1, $permissions);
        $this->assertSame($permission, $permissions->first());
    }

    /**
     * 测试权限列表渲染
     */
    public function testRenderPermissionList(): void
    {
        $permissions = ['user_manage', 'role_manage', 'system_config'];
        $this->role->setPermissions($permissions);

        $result = $this->role->renderPermissionList();
        $this->assertCount(3, $result);

        foreach ($result as $index => $item) {
            $this->assertArrayHasKey('text', $item);
            $this->assertArrayHasKey('fontStyle', $item);
            $this->assertEquals($permissions[$index], $item['text']);
            $this->assertEquals(['fontSize' => '12px'], $item['fontStyle']);
        }
    }

    /**
     * 测试层级角色默认值处理
     */
    public function testGetHierarchicalRolesWithNullValue(): void
    {
        $this->role->setHierarchicalRoles(null);
        $this->assertEquals([], $this->role->getHierarchicalRoles());
    }

    /**
     * 测试普通数组表示
     */
    public function testRetrievePlainArray(): void
    {
        // 设置 ID
        $reflection = new \ReflectionClass($this->role);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->role, 1);

        $this->role->setName('ROLE_ADMIN');
        $this->role->setTitle('系统管理员');
        $this->role->setValid(true);
        $this->role->setHierarchicalRoles(['ROLE_USER']);

        $now = new \DateTimeImmutable();
        $this->role->setCreateTime($now);
        $this->role->setUpdateTime($now);

        $result = $this->role->retrievePlainArray();
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('hierarchicalRoles', $result);

        $this->assertEquals(1, $result['id']);
        $this->assertEquals('ROLE_ADMIN', $result['name']);
        $this->assertEquals('系统管理员', $result['title']);
        $this->assertTrue($result['valid']);
        $this->assertEquals(['ROLE_USER'], $result['hierarchicalRoles']);

        // 测试时间戳数组
        $timestampArray = $this->role->retrieveTimestampArray();
        $this->assertArrayHasKey('createTime', $timestampArray);
        $this->assertArrayHasKey('updateTime', $timestampArray);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $timestampArray['createTime']);
        $this->assertEquals($now->format('Y-m-d H:i:s'), $timestampArray['updateTime']);
    }

    /**
     * 测试管理员数组表示
     */
    public function testRetrieveAdminArray(): void
    {
        // 设置基础属性
        $reflection = new \ReflectionClass($this->role);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->role, 1);

        $this->role->setName('ROLE_ADMIN');
        $this->role->setTitle('系统管理员');
        $this->role->setValid(true);
        $this->role->setPermissions(['user_manage', 'role_manage']);

        $result = $this->role->retrieveAdminArray();
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('permissions', $result);
        $this->assertArrayHasKey('userCount', $result);

        $this->assertEquals(1, $result['id']);
        $this->assertEquals('ROLE_ADMIN', $result['name']);
        $this->assertEquals('系统管理员', $result['title']);
        $this->assertTrue($result['valid']);
        $this->assertEquals(['user_manage', 'role_manage'], $result['permissions']);
        $this->assertEquals(0, $result['userCount']); // 没有用户时为0
    }

    /**
     * 辅助方法：获取对象的私有属性
     */
    private function getObjectProperty(object $object, string $propertyName): mixed
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
