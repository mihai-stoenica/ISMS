<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findBySearch(?string $search, ?string $categoryId = null) : Query
    {
        $qb = $this->createQueryBuilder('p');

        if($search) {
            $qb->where('p.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        if($categoryId) {
            $qb->andWhere('p.category = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }
        return $qb->getQuery();
    }
}
