<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'This email already exists.', errorPath: 'email')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private ?bool $is_accepted = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?SupplierProfile $supplierProfile = null;

    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'manager')]
    private Collection $createdTasks;

    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'employee')]
    private Collection $assignedTasks;

    public function __construct()
    {
        $this->createdTasks = new ArrayCollection();
        $this->assignedTasks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    public function isAccepted(): ?bool
    {
        return $this->is_accepted;
    }

    public function setIsAccepted(bool $is_accepted): static
    {
        $this->is_accepted = $is_accepted;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSupplierProfile(): ?SupplierProfile
    {
        return $this->supplierProfile;
    }

    public function setSupplierProfile(SupplierProfile $supplierProfile): static
    {
        if ($supplierProfile->getUser() !== $this) {
            $supplierProfile->setUser($this);
        }

        $this->supplierProfile = $supplierProfile;

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getCreatedTasks(): Collection
    {
        return $this->createdTasks;
    }

    public function addCreatedTask(Task $task): static
    {
        if (!$this->createdTasks->contains($task)) {
            $this->createdTasks->add($task);
            $task->setManager($this);
        }

        return $this;
    }

    public function removeCreatedTask(Task $task): static
    {
        if ($this->createdTasks->removeElement($task)) {
            if ($task->getManager() === $this) {
                $task->setManager(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getAssignedTasks(): Collection
    {
        return $this->assignedTasks;
    }

    public function addAssignedTask(Task $task): static
    {
        if (!$this->assignedTasks->contains($task)) {
            $this->assignedTasks->add($task);
            $task->setEmployee($this);
        }

        return $this;
    }

    public function removeAssignedTask(Task $task): static
    {
        if ($this->assignedTasks->removeElement($task)) {
            if ($task->getEmployee() === $this) {
                $task->setEmployee(null);
            }
        }

        return $this;
    }

}
