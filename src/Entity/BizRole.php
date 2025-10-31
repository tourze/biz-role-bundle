<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\PlainArrayInterface;
use Tourze\BizRoleBundle\Repository\BizRoleRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\RBAC\Core\Level0\Role;

/**
 * @implements PlainArrayInterface<string, mixed>
 * @implements AdminArrayInterface<string, mixed>
 */
#[ORM\Entity(repositoryClass: BizRoleRepository::class)]
#[ORM\Table(name: 'biz_role', options: ['comment' => '系统角色'])]
class BizRole implements \Stringable, PlainArrayInterface, AdminArrayInterface, Role
{
    use TimestampableAware;
    use BlameableAware;
    use IpTraceableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private int $id = 0;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '名称'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 80)]
    private ?string $name = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '标题'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $title = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '是否系统管理员'])]
    #[Assert\Type(type: 'bool')]
    private ?bool $admin = false;

    /**
     * @var array<string>
     */
    #[TrackColumn]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '拥有权限'])]
    #[Assert\Type(type: 'array')]
    private array $permissions = [];

    /**
     * @var Collection<int, UserInterface>
     */
    #[Ignore]
    #[ORM\ManyToMany(targetEntity: UserInterface::class, mappedBy: 'assignRoles', fetch: 'EXTRA_LAZY')]
    private Collection $users;

    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['default' => 1, 'comment' => '是否有效'])]
    #[Assert\Type(type: 'bool')]
    private ?bool $valid = true;

    #[TrackColumn]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '自定义菜单JSON'])]
    #[Assert\Length(max: 65535)]
    private ?string $menuJson = '';

    /**
     * @var array<string>
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '要排除的权限'])]
    #[Assert\Type(type: 'array')]
    private array $excludePermissions = [];

    /**
     * @var array<string>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '继承角色'])]
    #[Assert\Type(type: 'array')]
    private ?array $hierarchicalRoles = ['ROLE_OPERATOR'];

    /**
     * @var Collection<int, RoleEntityPermission>
     */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: RoleEntityPermission::class, mappedBy: 'role')]
    private Collection $dataPermissions;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->dataPermissions = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (0 === $this->getId()) {
            return '';
        }

        return sprintf('%s(%s)', $this->getTitle(), $this->getName());
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name ?? '';
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getTitle(): string
    {
        return $this->title ?? '';
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return array<string>
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @param array<string>|null $permissions
     */
    public function setPermissions(?array $permissions): void
    {
        $this->permissions = $permissions ?? [];
    }

    /**
     * @return Collection<int, UserInterface>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(UserInterface $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            if (method_exists($user, 'addAssignRole')) {
                $user->addAssignRole($this);
            }
        }

        return $this;
    }

    public function removeUser(UserInterface $user): self
    {
        if ($this->users->removeElement($user)) {
            if (method_exists($user, 'removeAssignRole')) {
                $user->removeAssignRole($this);
            }
        }

        return $this;
    }

    public function isAdmin(): ?bool
    {
        return $this->admin;
    }

    public function setAdmin(?bool $admin): void
    {
        $this->admin = $admin;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getMenuJson(): ?string
    {
        return $this->menuJson;
    }

    public function setMenuJson(?string $menuJson): void
    {
        $this->menuJson = $menuJson;
    }

    /**
     * @return array<string>
     */
    public function getExcludePermissions(): array
    {
        return $this->excludePermissions;
    }

    /**
     * @param array<string>|null $excludePermissions
     */
    public function setExcludePermissions(?array $excludePermissions): void
    {
        $this->excludePermissions = $excludePermissions ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function renderPermissionList(): array
    {
        $res = [];
        foreach ($this->getPermissions() as $permission) {
            $res[] = [
                'text' => $permission,
                'fontStyle' => ['fontSize' => '12px'],
            ];
        }

        return $res;
    }

    /**
     * @return array<string>
     */
    public function getHierarchicalRoles(): array
    {
        if (null === $this->hierarchicalRoles || [] === $this->hierarchicalRoles) {
            return [];
        }

        return $this->hierarchicalRoles;
    }

    /**
     * @param array<string>|null $hierarchicalRoles
     */
    public function setHierarchicalRoles(?array $hierarchicalRoles): void
    {
        $this->hierarchicalRoles = $hierarchicalRoles;
    }

    /**
     * @return Collection<int, RoleEntityPermission>
     */
    public function getDataPermissions(): Collection
    {
        return $this->dataPermissions;
    }

    public function addDataPermission(RoleEntityPermission $dataPermission): self
    {
        if (!$this->dataPermissions->contains($dataPermission)) {
            $this->dataPermissions->add($dataPermission);
            $dataPermission->setRole($this);
        }

        return $this;
    }

    public function removeDataPermission(RoleEntityPermission $dataPermission): self
    {
        // set the owning side to null (unless already changed)
        if ($this->dataPermissions->removeElement($dataPermission) && $dataPermission->getRole() === $this) {
            $dataPermission->setRole(null);
        }

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function retrievePlainArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'valid' => $this->isValid(),
            'hierarchicalRoles' => $this->getHierarchicalRoles(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveAdminArray(): array
    {
        return [
            ...$this->retrievePlainArray(),
            'permissions' => $this->getPermissions(),
            'userCount' => $this->getUsers()->count(),
        ];
    }
}
