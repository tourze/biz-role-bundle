<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\BizRoleBundle\Exception\RoleException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(RoleException::class)]
class RoleExceptionTest extends AbstractExceptionTestCase
{
    public function testFailedToCreateOrFindRole(): void
    {
        $name = 'test-role';
        $exception = RoleException::failedToCreateOrFindRole($name);

        $this->assertInstanceOf(RoleException::class, $exception);
        $this->assertSame(sprintf('Failed to create or find role "%s"', $name), $exception->getMessage());
    }

    public function testExtendsRuntimeException(): void
    {
        $exception = RoleException::failedToCreateOrFindRole('test');
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }
}
