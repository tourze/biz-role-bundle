<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\Tests\Fixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\BizRoleBundle\Entity\BizRole;

/**
 * 测试用户实体
 *
 * @internal 仅用于测试环境
 */
#[ORM\Entity]
#[ORM\Table(name: 'test_user')]
class TestUser implements UserInterface, \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 180, unique: true)]
    private string $username;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    /**
     * @var Collection<int, BizRole>
     */
    #[ORM\ManyToMany(targetEntity: BizRole::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: 'test_user_biz_role')]
    private Collection $assignRoles;

    public function __construct(string $username = 'test_user')
    {
        $this->username = $username;
        $this->assignRoles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // 测试用户不需要清除凭据
    }

    /**
     * @return Collection<int, BizRole>
     */
    public function getAssignRoles(): Collection
    {
        return $this->assignRoles;
    }

    public function addAssignRole(BizRole $role): self
    {
        if (!$this->assignRoles->contains($role)) {
            $this->assignRoles->add($role);
        }

        return $this;
    }

    public function removeAssignRole(BizRole $role): self
    {
        $this->assignRoles->removeElement($role);

        return $this;
    }

    public function __toString(): string
    {
        return $this->username;
    }
}
