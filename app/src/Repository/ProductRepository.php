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

    public function findBySearch(?string $search) : Query
    {
        $qb = $this->createQueryBuilder('p');

        if($search) {
            $qb->where('p.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }
        return $qb->getQuery();
    }
}
