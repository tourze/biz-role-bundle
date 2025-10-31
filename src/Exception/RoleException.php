<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\Exception;

class RoleException extends \RuntimeException
{
    public static function failedToCreateOrFindRole(string $name): self
    {
        return new self(sprintf('Failed to create or find role "%s"', $name));
    }
}
