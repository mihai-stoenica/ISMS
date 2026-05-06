<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function getSumsGained(string $groupBy, ?\DateTime $start, ?\DateTime $end)
    {
        $dateFormat = ($groupBy === 'day') ? '%Y-%m-%d' : '%Y-%m';
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT DATE_FORMAT(o.completed_at, :format) as period, SUM(o.total_price) as total
        FROM `order` o
        WHERE o.status = :status
    ';

        $params = [
            'format' => $dateFormat,
            'status' => 'DONE'
        ];

        if ($start && $end) {
            $sql .= ' AND o.completed_at BETWEEN :start AND :end';
            $params['start'] = $start->format('Y-m-d');
            $params['end'] = $end->format('Y-m-d');
        } elseif ($start) {
            $sql .= ' AND o.completed_at >= :start';
            $params['start'] = $start->format('Y-m-d');
        } elseif ($end) {
            $sql .= ' AND o.completed_at <= :end';
            $params['end'] = $end->format('Y-m-d');
        }

        $sql .= ' GROUP BY period ORDER BY period ASC';

        $resultSet = $conn->executeQuery($sql, $params);
        return $resultSet->fetchAllAssociative();
    }
}
