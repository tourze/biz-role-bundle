<?php

declare(strict_types=1);

namespace Tourze\BizRoleBundle\Tests\Fixtures\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\BizRoleBundle\Entity\BizRole;

/**
 * Test user entity for BizRoleBundle tests
 * This entity exists only to satisfy Doctrine's ManyToMany relationship validation
 */
#[ORM\Entity]
class TestUser implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $username = '';

    /**
     * @var Collection<int, BizRole>
     */
    #[ORM\ManyToMany(targetEntity: BizRole::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: 'test_user_biz_role')]
    private Collection $assignRoles;

    public function __construct()
    {
        $this->assignRoles = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
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

    // UserInterface methods
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // Nothing to erase
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }
}
