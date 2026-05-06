<?php

namespace App\Repository;

use App\Entity\Contract;
use App\Enum\ContractStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contract>
 */
class ContractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contract::class);
    }

    public function getSumsSpent(string $groupBy, ?\DateTime $start, ?\DateTime $end) : array
    {
        $dateFormat = ($groupBy === 'day') ? '%Y-%m-%d' : '%Y-%m';

        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT DATE_FORMAT(date, :format) as period, SUM(total_cost) as total
            FROM contract
            WHERE status = :status
        ';

        $params = [
            'format' => $dateFormat,
            'status' => 'DONE'
        ];

        if ($start && $end) {
            $sql .= ' AND date BETWEEN :start AND :end';
            $params['start'] = $start->format('Y-m-d');
            $params['end'] = $end->format('Y-m-d');
        } else if($start) {
            $sql .= ' AND date >= :start';
            $params['start'] = $start->format('Y-m-d');
        } else if($end) {
            $sql .= ' AND date <= :end';
            $params['end'] = $end->format('Y-m-d');
        }

        $sql .= ' GROUP BY period ORDER BY period ASC';

        $resultSet = $conn->executeQuery($sql, $params);
        return $resultSet->fetchAllAssociative();
    }

    public function getSupplierEfficiency(?\DateTime $start, ?\DateTime $end) : array
    {
        $conn = $this->getEntityManager()->getConnection();
        $params = [];

        $subqueryDateLogic = '';
        if ($start && $end) {
            $subqueryDateLogic = ' AND o.completed_at BETWEEN :start AND :end';
        } elseif ($start) {
            $subqueryDateLogic = ' AND o.completed_at >= :start';
        } elseif ($end) {
            $subqueryDateLogic = ' AND o.completed_at <= :end';
        }

        $contractDateLogic = '';
        if ($start && $end) {
            $contractDateLogic = ' AND c.date BETWEEN :start AND :end';
            $params['start'] = $start->format('Y-m-d');
            $params['end'] = $end->format('Y-m-d');
        } elseif ($start) {
            $contractDateLogic = ' AND c.date >= :start';
            $params['start'] = $start->format('Y-m-d');
        } elseif ($end) {
            $contractDateLogic = ' AND c.date <= :end';
            $params['end'] = $end->format('Y-m-d');
        }

        $sql = '
            SELECT
                u.name as supplier_name,
                COUNT(DISTINCT c.id) as total_contracts,
                SUM(c.total_cost) as total_investment,
                COALESCE(rev.total_revenue, 0) as total_revenue,
                COUNT(DISTINCT sp_main.product_id) as product_diversity
            FROM supplier_profile s
            JOIN `user` u ON s.user_id = u.id
            LEFT JOIN contract c ON s.id = c.supplier_id AND c.status = "done" ' . $contractDateLogic . '
            LEFT JOIN supplier_product sp_main ON s.id = sp_main.supplier_id
            LEFT JOIN (
                SELECT sp.supplier_id, SUM(po.quantity * p.selling_price) as total_revenue
                FROM product_order po
                JOIN `order` o ON po.order_id = o.id
                JOIN product p ON po.product_id = p.id
                JOIN supplier_product sp ON po.product_id = sp.product_id
                WHERE o.status = "done" ' . $subqueryDateLogic . '
                GROUP BY sp.supplier_id
            ) rev ON rev.supplier_id = s.id
            GROUP BY s.id, u.name, rev.total_revenue
            HAVING total_contracts > 0
            ORDER BY total_revenue DESC
        ';

        return $conn->executeQuery($sql, $params)->fetchAllAssociative();
    }
}
