<?php

namespace App\Service;

use App\Enum\Location;
use App\Repository\ContractRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class AnalyticsService
{
    public function __construct(
        private ProductRepository $productRepo,
        private ContractRepository $contractRepo,
        private OrderRepository $orderRepo
    ) {}

    public function getOccupationStats(): array
    {
        $storageLocations = array_filter(
            Location::cases(),
            fn(Location $loc) => !$loc->isRamp()
        );

        $totalSlots = count($storageLocations);
        $occupiedSlots = (int)$this->productRepo->findOccupiedSlots();
        $emptySlots = $totalSlots - $occupiedSlots;

        return [
            'labels' => ['Occupied', 'Empty'],
            'data' => [$occupiedSlots, $emptySlots],
            'percentage' => $totalSlots > 0 ? round(($occupiedSlots / $totalSlots) * 100, 2) : 0,
            'total' => $totalSlots
        ];
    }

    public function getDynamicChartFinancialData(string $range = 'month', ?string $start = null, ?string $end = null): array
    {
        $startDate = $start ? new \DateTime($start) : null;
        $endDate = $end ? new \DateTime($end) : null;

        $sumSpentRaw = $this->contractRepo->getSumsSpent($range, $startDate, $endDate);

        $sumGainedRaw = $this->orderRepo->getSumsGained($range, $startDate, $endDate);

        $allPeriods = array_unique(array_merge(
            array_column($sumSpentRaw, 'period'),
            array_column($sumGainedRaw, 'period')
        ));
        sort($allPeriods);

        $spentMap = array_column($sumSpentRaw, 'total', 'period');
        $gainedMap = array_column($sumGainedRaw, 'total', 'period');

        $finalLabels = [];
        $finalSpent = [];
        $finalGained = [];
        $finalProfit = [];

        foreach ($allPeriods as $period) {
            $finalLabels[] = $period;

            $spentValue = (float)($spentMap[$period] ?? 0);
            $gainedValue = (float)($gainedMap[$period] ?? 0);

            $finalSpent[] = $spentValue;
            $finalGained[] = $gainedValue;
            $finalProfit[] = $gainedValue - $spentValue;
        }

        return [
            'labels' => $finalLabels,
            'spent' => $finalSpent,
            'gained' => $finalGained,
            'profit' => $finalProfit
        ];
    }
}
