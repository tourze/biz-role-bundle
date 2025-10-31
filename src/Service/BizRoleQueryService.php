<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\Service;

use Tourze\BizRoleBundle\Entity\BizRole;
use Tourze\BizRoleBundle\Repository\BizRoleRepository;

/**
 * 角色查询服务
 */
final class BizRoleQueryService
{
    public function __construct(
        private readonly BizRoleRepository $bizRoleRepository,
    ) {
    }

    /**
     * @return BizRole[]
     */
    public function getValidRoles(): array
    {
        return $this->bizRoleRepository->loadValidRoles();
    }

    /**
     * @return BizRole[]
     */
    public function searchRoles(string $query): array
    {
        $result = $this->bizRoleRepository->createQueryBuilder('r')
            ->where('r.title LIKE :query OR r.name LIKE :query')
            ->andWhere('r.valid = true')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult()
        ;

        return $result ?? [];
    }

    /**
     * @param BizRole[] $roles
     * @return array<array{id: int, text: string}>
     */
    public function formatRolesForAutocomplete(array $roles): array
    {
        $results = [];
        foreach ($roles as $role) {
            $results[] = [
                'id' => $role->getId(),
                'text' => $role->getTitle() . ' (' . $role->getName() . ')',
            ];
        }

        return $results;
    }
}
