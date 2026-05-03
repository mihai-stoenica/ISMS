<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\SupplierProduct;
use App\Entity\User;
use App\Repository\SupplierProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductsCatalogService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function mapSupplierProduct(User $user, Product $product, string $price) : ?string
    {
        if(!$price || $price < 0.01) {
            return "The price must be a positive number";
        }
        $supplierProduct = new SupplierProduct();
        $supplierProduct->setProduct($product);
        $supplierProduct->setSupplier($user->getSupplierProfile());
        $supplierProduct->setPurchasePrice($price);

        $this->entityManager->persist($supplierProduct);
        $this->entityManager->flush();

        return null;
    }

    public function unsellProduct(User $user, Product $product) : ?string
    {
        $supplierProduct = $this->entityManager->getRepository(SupplierProduct::class)->findOneBy([
            'supplier' => $user->getSupplierProfile(),
            'product' => $product,
        ]);

        if(!$supplierProduct) {
            return "The supplier does not sell this product";
        }

        $this->entityManager->remove($supplierProduct);
        $this->entityManager->flush();

        return null;
    }
}
