<?php

namespace App\Entity;

use App\Repository\SupplierProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SupplierProfileRepository::class)]
class SupplierProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $uniqueIdentifier = null;

    #[ORM\Column(length: 10)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\OneToOne(inversedBy: 'supplierProfile', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, SupplierProduct>
     */
    #[ORM\OneToMany(targetEntity: SupplierProduct::class, mappedBy: 'supplier')]
    private Collection $supplierProducts;

    public function __construct()
    {
        $this->supplierProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUniqueIdentifier(): ?string
    {
        return $this->uniqueIdentifier;
    }

    public function setUniqueIdentifier(string $uniqueIdentifier): static
    {
        $this->uniqueIdentifier = $uniqueIdentifier;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, SupplierProduct>
     */
    public function getSupplierProducts(): Collection
    {
        return $this->supplierProducts;
    }

    public function addSupplierProduct(SupplierProduct $supplierProduct): static
    {
        if (!$this->supplierProducts->contains($supplierProduct)) {
            $this->supplierProducts->add($supplierProduct);
            $supplierProduct->setSupplier($this);
        }

        return $this;
    }

    public function removeSupplierProduct(SupplierProduct $supplierProduct): static
    {
        if ($this->supplierProducts->removeElement($supplierProduct)) {
            if ($supplierProduct->getSupplier() === $this) {
                $supplierProduct->setSupplier(null);
            }
        }

        return $this;
    }
}
