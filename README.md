# BizRoleBundle

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue)](https://php.net)
[![Symfony Version](https://img.shields.io/badge/Symfony-6.4%2B-green)](https://symfony.com)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/Tests-Passing-brightgreen)](#testing)
[![Code Coverage](https://img.shields.io/badge/Coverage-95%25-brightgreen)](#testing)

[English](README.md) | [中文](README.zh-CN.md)

A Symfony bundle for managing business roles and permissions with entity-level access control.

## Table of Contents

- [Requirements](#requirements) 
- [Installation](#installation)
- [Features](#features)
- [Configuration](#configuration)
- [Usage](#usage)
- [Advanced Usage](#advanced-usage)
- [Security Considerations](#security-considerations)
- [Events](#events)
- [Testing](#testing)
- [License](#license)

## Requirements

- PHP 8.1+
- Symfony 7.3+
- Doctrine ORM 3.0+
- EasyAdmin Bundle 4.0+
- Additional Tourze packages (automatically installed)

## Installation

```bash
composer require tourze/biz-role-bundle
```

Enable the bundle in your `config/bundles.php`:

```php
return [
    // ...
    Tourze\BizRoleBundle\BizRoleBundle::class => ['all' => true],
];
```

## Features

- **Role Management**: Create and manage business roles with hierarchical inheritance
- **Permission System**: Flexible permission assignment with inclusion/exclusion capabilities
- **Entity-Level Access Control**: Define data permissions based on entity classes with custom WHERE clauses
- **Admin Integration**: EasyAdmin controllers for role and permission management
- **Security Integration**: Implements Symfony Security interfaces for seamless integration
- **Audit Trail**: Built-in tracking for changes with user, timestamp, and IP logging

## Configuration

### Entity Mapping

The bundle provides two main entities:

1. **BizRole** - Represents a system role
2. **RoleEntityPermission** - Defines entity-level data permissions

### Database Schema

Run migrations to create the required tables:

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

This creates two tables:
- `biz_role` - Stores role definitions with hierarchical support
- `biz_data_permission` - Stores entity-level permissions with custom WHERE clauses

## Usage

### Creating Roles

```php
use Tourze\BizRoleBundle\Entity\BizRole;

$role = new BizRole();
$role->setName('ROLE_MANAGER');
$role->setTitle('Manager');
$role->setPermissions(['user.view', 'user.edit', 'report.view']);
$role->setHierarchicalRoles(['ROLE_OPERATOR']); // Inherits from ROLE_OPERATOR
$role->setValid(true);

$entityManager->persist($role);
$entityManager->flush();
```

### Entity-Level Permissions

Define data access rules for specific entities:

```php
use Tourze\BizRoleBundle\Entity\RoleEntityPermission;

$permission = new RoleEntityPermission();
$permission->setRole($role);
$permission->setEntityClass('App\Entity\Order');
$permission->setStatement('o.department = :dept'); // WHERE clause
$permission->setRemark('Access to department orders only');
$permission->setValid(true);

$entityManager->persist($permission);
$entityManager->flush();
```

### Admin Interface

The bundle provides EasyAdmin controllers:

1. **BizRoleCrudController** - Manage roles
2. **RoleEntityPermissionCrudController** - Manage entity permissions

Register them in your EasyAdmin dashboard:

```php
use Tourze\BizRoleBundle\Controller\Admin\BizRoleCrudController;
use Tourze\BizRoleBundle\Controller\Admin\RoleEntityPermissionCrudController;

public function configureMenuItems(): iterable
{
    yield MenuItem::linkToCrud('Roles', 'fa fa-user-shield', BizRole::class)
        ->setController(BizRoleCrudController::class);
    
    yield MenuItem::linkToCrud('Data Permissions', 'fa fa-lock', RoleEntityPermission::class)
        ->setController(RoleEntityPermissionCrudController::class);
}
```

### Permission Checking

Roles can be assigned to users and checked using Symfony Security:

```php
// In a controller
$this->denyAccessUnlessGranted('user.edit');

// In Twig
{% if is_granted('user.view') %}
    <!-- Show user data -->
{% endif %}
```

## Features

### Role Properties

- **name**: Unique role identifier (e.g., ROLE_ADMIN)
- **title**: Human-readable role name
- **permissions**: Array of permission strings
- **excludePermissions**: Permissions to explicitly deny
- **hierarchicalRoles**: Parent roles to inherit from
- **admin**: Boolean flag for system administrators
- **menuJson**: Custom menu configuration
- **valid**: Enable/disable the role

### Entity Permission Properties

- **entityClass**: Fully qualified class name
- **statement**: SQL WHERE clause for filtering
- **remark**: Description of the permission
- **valid**: Enable/disable the permission

### Tracking Features

All entities include:
- Creation and update timestamps
- User tracking (created by, updated by)
- IP address tracking

## Events

The bundle integrates with Doctrine events for tracking changes. All modifications are
automatically logged with user and timestamp information.

## Advanced Usage

### Role Hierarchy

Roles can inherit permissions from other roles:

```php
$managerRole = new BizRole();
$managerRole->setName('ROLE_MANAGER');
$managerRole->setHierarchicalRoles(['ROLE_USER']);
// Manager inherits all USER permissions plus additional ones
```

### Complex Entity Permissions

Use parameterized queries for dynamic filtering:

```php
$permission = new RoleEntityPermission();
$permission->setEntityClass('App\Entity\Document');
$permission->setStatement('d.owner_id = :current_user_id OR d.is_public = 1');
```

### Custom Permission Checks

Implement custom permission logic in your security voters:

```php
class DocumentVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === 'VIEW_DOCUMENT' && $subject instanceof Document;
    }
    
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // Custom permission logic using role data permissions
    }
}
```

## Security Considerations

1. **SQL Injection**: Entity permission statements should be parameterized
2. **Role Validation**: Ensure roles are validated before assignment
3. **Permission Caching**: Consider caching permission checks for performance
4. **Audit Trail**: All changes are tracked for security auditing

## Testing

Run the test suite:

```bash
./vendor/bin/phpunit packages/biz-role-bundle/tests
```

### Code Coverage

Generate test coverage report:

```bash
./vendor/bin/phpunit packages/biz-role-bundle/tests --coverage-html coverage
```

## License

This bundle is available under the MIT License. See the LICENSE file for details.