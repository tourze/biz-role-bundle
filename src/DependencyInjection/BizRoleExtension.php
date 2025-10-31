<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class BizRoleExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return dirname(__DIR__) . '/Resources/config';
    }
}
