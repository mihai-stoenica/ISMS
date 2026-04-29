<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findBySearchParams(?string $status, ?string $search, ?string $order = null, ?User $employee = null): array
    {
        $queryBuilder = $this->createQueryBuilder('t');

        if($employee){
            $queryBuilder
                ->andWhere('t.employee = :employee')
                ->setParameter('employee', $employee);
        }

        if($status) {
            $queryBuilder
                ->andWhere('t.status = :status')
                ->setParameter('status', $status);
        }

        if($search) {
            $queryBuilder
                ->leftJoin('t.product', 'p')
                ->leftJoin('t.manager', 'm')
                ->leftJoin('t.employee', 'e')
                ->andWhere('p.name LIKE :search OR e.name LIKE :search OR m.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        if($order == 'urgent') {
            $queryBuilder
                ->orderBy('t.created_at', 'ASC');

        }
        return $queryBuilder->getQuery()->getResult();
    }
}
