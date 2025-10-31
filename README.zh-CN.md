# BizRoleBundle

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue)](https://php.net)
[![Symfony Version](https://img.shields.io/badge/Symfony-6.4%2B-green)](https://symfony.com)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/Tests-Passing-brightgreen)](#testing)
[![Code Coverage](https://img.shields.io/badge/Coverage-95%25-brightgreen)](#testing)

[English](README.md) | [中文](README.zh-CN.md)

一个用于管理业务角色和权限的 Symfony Bundle，支持实体级访问控制。

## 目录

- [系统要求](#系统要求)
- [安装](#安装)
- [功能特性](#功能特性)
- [配置](#配置)
- [使用方法](#使用方法)
- [高级用法](#高级用法)
- [安全注意事项](#安全注意事项)
- [事件](#事件)
- [测试](#测试)
- [许可证](#许可证)

## 系统要求

- PHP 8.1+
- Symfony 7.3+
- Doctrine ORM 3.0+
- EasyAdmin Bundle 4.0+
- 其他 Tourze 包（自动安装）

## 安装

```bash
composer require tourze/biz-role-bundle
```

在 `config/bundles.php` 中启用 bundle：

```php
return [
    // ...
    Tourze\BizRoleBundle\BizRoleBundle::class => ['all' => true],
];
```

## 功能特性

- **角色管理**：创建和管理具有层级继承的业务角色
- **权限系统**：灵活的权限分配，支持包含和排除权限
- **实体级访问控制**：基于实体类定义数据权限，支持自定义 WHERE 条件
- **管理界面集成**：提供 EasyAdmin 控制器进行角色和权限管理
- **安全集成**：实现 Symfony Security 接口，无缝集成
- **审计追踪**：内置变更跟踪，记录用户、时间戳和 IP 地址

## 配置

### 实体映射

Bundle 提供两个主要实体：

1. **BizRole** - 表示系统角色
2. **RoleEntityPermission** - 定义实体级数据权限

### 数据库架构

运行迁移创建所需的表：

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

这将创建两个表：
- `biz_role` - 存储角色定义，支持层级结构
- `biz_data_permission` - 存储实体级权限，支持自定义 WHERE 条件

## 使用方法

### 创建角色

```php
use Tourze\BizRoleBundle\Entity\BizRole;

$role = new BizRole();
$role->setName('ROLE_MANAGER');
$role->setTitle('管理员');
$role->setPermissions(['user.view', 'user.edit', 'report.view']);
$role->setHierarchicalRoles(['ROLE_OPERATOR']); // 继承自 ROLE_OPERATOR
$role->setValid(true);

$entityManager->persist($role);
$entityManager->flush();
```

### 实体级权限

为特定实体定义数据访问规则：

```php
use Tourze\BizRoleBundle\Entity\RoleEntityPermission;

$permission = new RoleEntityPermission();
$permission->setRole($role);
$permission->setEntityClass('App\Entity\Order');
$permission->setStatement('o.department = :dept'); // WHERE 条件
$permission->setRemark('仅访问部门订单');
$permission->setValid(true);

$entityManager->persist($permission);
$entityManager->flush();
```

### 管理界面

Bundle 提供 EasyAdmin 控制器：

1. **BizRoleCrudController** - 管理角色
2. **RoleEntityPermissionCrudController** - 管理实体权限

在 EasyAdmin 仪表板中注册：

```php
use Tourze\BizRoleBundle\Controller\Admin\BizRoleCrudController;
use Tourze\BizRoleBundle\Controller\Admin\RoleEntityPermissionCrudController;

public function configureMenuItems(): iterable
{
    yield MenuItem::linkToCrud('角色管理', 'fa fa-user-shield', BizRole::class)
        ->setController(BizRoleCrudController::class);
    
    yield MenuItem::linkToCrud('数据权限', 'fa fa-lock', RoleEntityPermission::class)
        ->setController(RoleEntityPermissionCrudController::class);
}
```

### 权限检查

角色可以分配给用户并使用 Symfony Security 进行检查：

```php
// 在控制器中
$this->denyAccessUnlessGranted('user.edit');

// 在 Twig 模板中
{% if is_granted('user.view') %}
    <!-- 显示用户数据 -->
{% endif %}
```

## 功能详解

### 角色属性

- **name**：唯一角色标识符（如 ROLE_ADMIN）
- **title**：人类可读的角色名称
- **permissions**：权限字符串数组
- **excludePermissions**：明确拒绝的权限
- **hierarchicalRoles**：要继承的父角色
- **admin**：系统管理员标志
- **menuJson**：自定义菜单配置
- **valid**：启用/禁用角色

### 实体权限属性

- **entityClass**：完全限定的类名
- **statement**：用于过滤的 SQL WHERE 子句
- **remark**：权限描述
- **valid**：启用/禁用权限

### 跟踪功能

所有实体包括：
- 创建和更新时间戳
- 用户跟踪（创建者、更新者）
- IP 地址跟踪

## 事件

Bundle 集成了 Doctrine 事件来跟踪变更。所有修改都会自动记录用户和时间戳信息。

## 高级用法

### 角色层级结构

角色可以从其他角色继承权限：

```php
$managerRole = new BizRole();
$managerRole->setName('ROLE_MANAGER');
$managerRole->setHierarchicalRoles(['ROLE_USER']);
// 管理员继承所有用户权限及额外权限
```

### 复杂实体权限

使用参数化查询进行动态过滤：

```php
$permission = new RoleEntityPermission();
$permission->setEntityClass('App\\Entity\\Document');
$permission->setStatement('d.owner_id = :current_user_id OR d.is_public = 1');
```

### 自定义权限检查

在安全投票器中实现自定义权限逻辑：

```php
class DocumentVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === 'VIEW_DOCUMENT' && $subject instanceof Document;
    }
    
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // 使用角色数据权限的自定义权限逻辑
    }
}
```

## 安全注意事项

1. **SQL 注入**：实体权限语句应该参数化
2. **角色验证**：确保在分配前验证角色
3. **权限缓存**：考虑缓存权限检查以提高性能
4. **审计追踪**：所有变更都被跟踪用于安全审计

## 测试

运行测试套件：

```bash
./vendor/bin/phpunit packages/biz-role-bundle/tests
```

### 代码覆盖率

生成测试覆盖率报告：

```bash
./vendor/bin/phpunit packages/biz-role-bundle/tests --coverage-html coverage
```

## 许可证

此 Bundle 在 MIT 许可证下可用。详见 LICENSE 文件。