# RBAC Bundle

RBAC（基于角色的访问控制）能力的Symfony Bundle。

## 安装

```bash
composer require tourze/rbac-bundle
```

## 使用

### 注册Bundle

在`config/bundles.php`文件中注册Bundle：

```php
<?php

return [
    // ...
    Tourze\RBACBundle\RBACBundle::class => ['all' => true],
];
```

## 单元测试

在项目根目录运行测试：

```bash
./vendor/bin/phpunit packages/rbac-bundle/tests
```
