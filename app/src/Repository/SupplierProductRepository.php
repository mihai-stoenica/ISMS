<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\SupplierProduct;
use App\Entity\SupplierProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SupplierProduct>
 */
class SupplierProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupplierProduct::class);
    }

    public function findSellersForProduct(Product $product) : array
    {
        $qb = $this->createQueryBuilder('sp');

        $qb->innerJoin('sp.supplier', 'profile')
            ->addSelect('profile')
            ->innerJoin('profile.user', 'user')
            ->addSelect('user')
            ->where('sp.product = :product')
            ->setParameter('product', $product)
            ->orderBy('sp.purchasePrice', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
