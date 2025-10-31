<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\BizRoleBundle\Repository\RoleEntityPermissionRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Table(name: 'biz_data_permission', options: ['comment' => '角色实体数据权限'])]
#[ORM\UniqueConstraint(name: 'biz_data_permission_idx_uniq', columns: ['role_id', 'entity_class'])]
#[ORM\Entity(repositoryClass: RoleEntityPermissionRepository::class)]
class RoleEntityPermission implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'dataPermissions', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?BizRole $role = null;

    #[Groups(groups: ['admin_curd'])]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '实体类名'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $entityClass = null;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(type: Types::TEXT, options: ['comment' => 'WHERE语句'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 65535)]
    private ?string $statement = null;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    #[Assert\Length(max: 65535)]
    private ?string $remark = null;

    #[IndexColumn]
    #[TrackColumn]
    #[Groups(groups: ['admin_curd', 'restful_read', 'restful_read', 'restful_write'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    #[Assert\Type(type: 'bool')]
    private ?bool $valid = false;

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getRole(): ?BizRole
    {
        return $this->role;
    }

    public function setRole(?BizRole $role): void
    {
        $this->role = $role;
    }

    public function getEntityClass(): ?string
    {
        return $this->entityClass;
    }

    public function setEntityClass(string $entityClass): void
    {
        $this->entityClass = $entityClass;
    }

    public function getStatement(): ?string
    {
        return $this->statement;
    }

    public function setStatement(string $statement): void
    {
        $this->statement = $statement;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function __toString(): string
    {
        if (null === $this->getId()) {
            return '';
        }

        return sprintf('RoleEntityPermission %s (%s)', $this->getId(), $this->getEntityClass());
    }
}
