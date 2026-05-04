<?php

namespace App\Entity;

use App\Repository\SupplierProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SupplierProductRepository::class)]
class SupplierProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'supplierProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'supplierProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SupplierProfile $supplier = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $purchasePrice = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getSupplier(): ?SupplierProfile
    {
        return $this->supplier;
    }

    public function setSupplier(?SupplierProfile $supplier): static
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function getPurchasePrice(): ?float
    {
        return $this->purchasePrice;
    }

    public function setPurchasePrice(float $purchasePrice): static
    {
        $this->purchasePrice = $purchasePrice;

        return $this;
    }
}
