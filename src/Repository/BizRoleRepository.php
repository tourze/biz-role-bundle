<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Tourze\BizRoleBundle\Entity\BizRole;
use Tourze\BizRoleBundle\Exception\RoleException;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\RBAC\Core\Level0\Role;
use Tourze\RBAC\Core\Service\RoleLoaderInterface;

/**
 * @extends ServiceEntityRepository<BizRole>
 */
#[AsAlias(id: RoleLoaderInterface::class)]
#[AsRepository(entityClass: BizRole::class)]
final class BizRoleRepository extends ServiceEntityRepository implements RoleLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BizRole::class);
    }

    public function loadUserByName(string $name): ?Role
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * @return BizRole[]
     */
    public function loadValidRoles(): array
    {
        return $this->findBy([
            'valid' => true,
        ]);
    }

    public function findOrCreate(string $name, ?string $title = null): BizRole
    {
        // 使用 SQL 的 INSERT OR IGNORE 来原子性地处理创建
        $connection = $this->getEntityManager()->getConnection();

        try {
            // 首先尝试插入
            $connection->executeStatement(
                'INSERT OR IGNORE INTO biz_role (name, title, valid, admin, create_time, update_time) VALUES (?, ?, ?, ?, ?, ?)',
                [
                    $name,
                    $title ?? $name,
                    1, // valid = true
                    0, // admin = false
                    date('Y-m-d H:i:s'), // create_time
                    date('Y-m-d H:i:s'),  // update_time
                ]
            );
        } catch (\Exception) {
            // 如果插入失败，忽略异常
        }

        // 查找并返回角色
        $entity = $this->findOneBy(['name' => $name]);
        if (null === $entity) {
            throw RoleException::failedToCreateOrFindRole($name);
        }

        return $entity;
    }

    public function save(BizRole $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BizRole $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
