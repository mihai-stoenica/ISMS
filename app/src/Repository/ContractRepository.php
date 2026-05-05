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
}
