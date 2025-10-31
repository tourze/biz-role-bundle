<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\BizRoleBundle\BizRoleBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(BizRoleBundle::class)]
#[RunTestsInSeparateProcesses]
final class BizRoleBundleTest extends AbstractBundleTestCase
{
}
