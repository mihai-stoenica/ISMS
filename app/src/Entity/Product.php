<?php

namespace App\Entity;

use App\Enum\Location;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $sellingPrice = null;

    #[ORM\Column]
    private ?int $currentStock = null;

    #[ORM\Column]
    private ?int $lowStockThreshold = 100;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Category $category = null;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'product')]
    private Collection $tasks;

    #[ORM\Column(enumType: Location::class, nullable: true)]
    private ?Location $location = null;

    /**
     * @var Collection<int, SupplierProduct>
     */
    #[ORM\OneToMany(targetEntity: SupplierProduct::class, mappedBy: 'product')]
    private Collection $supplierProducts;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->supplierProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSellingPrice(): ?float
    {
        return $this->sellingPrice;
    }

    public function setSellingPrice(float $sellingPrice): static
    {
        $this->sellingPrice = $sellingPrice;

        return $this;
    }

    public function getCurrentStock(): ?int
    {
        return $this->currentStock;
    }

    public function setCurrentStock(int $currentStock): static
    {
        $this->currentStock = $currentStock;

        return $this;
    }

    public function getLowStockThreshold(): ?int
    {
        return $this->lowStockThreshold;
    }

    public function setLowStockThreshold(int $lowStockThreshold): static
    {
        $this->lowStockThreshold = $lowStockThreshold;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setProduct($this);
        }

        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            if ($task->getProduct() === $this) {
                $task->setProduct(null);
            }
        }

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): static
    {
        $this->location = $location;

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
            $supplierProduct->setProduct($this);
        }

        return $this;
    }

    public function removeSupplierProduct(SupplierProduct $supplierProduct): static
    {
        if ($this->supplierProducts->removeElement($supplierProduct)) {
            // set the owning side to null (unless already changed)
            if ($supplierProduct->getProduct() === $this) {
                $supplierProduct->setProduct(null);
            }
        }

        return $this;
    }
}
