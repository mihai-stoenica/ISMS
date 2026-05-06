<?php

namespace App\Service;

use App\Enum\Location;
use App\Repository\ContractRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class AnalyticsService
{
    public function __construct(
        private ProductRepository $productRepo,
        private ContractRepository $contractRepo,
        private OrderRepository $orderRepo,
        private ChartBuilderInterface $chartBuilder,
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
    private function getChartType(string $type): string
    {
        return $type === 'occupation' ? Chart::TYPE_DOUGHNUT : ($type === 'suppliers' ? Chart::TYPE_BAR : Chart::TYPE_LINE);
    }

    public function createChartByType(string $type, string $range, ?string $start, ?string $end): array
    {
        $chart = $this->chartBuilder->createChart($this->getChartType($type));
        $extraData = null;

        switch ($type) {
            case 'financial':
                $data = $this->getDynamicChartFinancialData($range, $start, $end);
                $chart->setData([
                    'labels' => $data['labels'],
                    'datasets' => [
                        ['label' => 'Spent', 'borderColor' => 'rgb(54, 162, 235)', 'data' => $data['spent'], 'tension' => 0.4],
                        ['label' => 'Gained', 'borderColor' => 'rgb(75, 192, 192)', 'data' => $data['gained'], 'tension' => 0.4],
                        ['label' => 'Profit', 'borderColor' => 'rgb(153, 102, 255)', 'data' => $data['profit'], 'tension' => 0.4]
                    ]
                ]);
                break;

            case 'occupation':
                $data = $this->getOccupationStats();
                $chart->setData([
                    'labels' => $data['labels'],
                    'datasets' => [['backgroundColor' => ['#ff6384', '#36a2eb'], 'data' => $data['data']]]
                ]);
                $extraData = ['percent' => $data['percentage'], 'total' => $data['total']];
                break;

            case 'suppliers':
                $rawData = $this->contractRepo->getSupplierEfficiency(
                    $start ? new \DateTime($start) : null,
                    $end ? new \DateTime($end) : null
                );

                $index = 0;
                $datasets = [];

                foreach ($rawData as $row) {
                    $diversity = (int)$row['product_diversity'];

                    $scaledRadius = min(max(sqrt($diversity) * 5, 5), 60);

                    $hue = ($index * 137.5) % 360;
                    $backgroundColor = "hsla({$hue}, 70%, 60%, 0.6)";
                    $borderColor = "hsl({$hue}, 70%, 60%)";

                    $datasets[] = [
                        'label' => $row['supplier_name'],
                        'data' => [[
                            'x' => (float)$row['total_investment'],
                            'y' => (float)$row['total_revenue'],
                            'r' => $scaledRadius
                        ]],
                        'backgroundColor' => $backgroundColor,
                        'borderColor' => $borderColor,
                    ];
                    $index++;
                }

                $chart = $this->chartBuilder->createChart(Chart::TYPE_BUBBLE);
                $chart->setData(['datasets' => $datasets]);
                $extraData = ['count' => count($rawData)];
                break;
        }

        $this->configureChartOptions($chart, $type);

        return ['chart' => $chart, 'extraData' => $extraData];
    }

    private function configureChartOptions(Chart $chart, string $type): void
    {
        $options = ['maintainAspectRatio' => false, 'responsive' => true];

        if ($type !== 'occupation') {
            $options['scales']['y'] = ['beginAtZero' => true];
        }

        $chart->setOptions($options);
    }

    public function getRawSupplierData(?string $start, ?string $end): array
    {
        $startDate = $start ? new \DateTime($start) : null;
        $endDate = $end ? new \DateTime($end) : null;

        return $this->contractRepo->getSupplierEfficiency($startDate, $endDate);
    }

    public function createCSV(string $type, string $range, ?string $start, ?string $end): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($type, $range, $start, $end) {
            $handle = fopen('php://output', 'w+');

            if ($type === 'financial') {
                fputcsv($handle, ['Period', 'Spent ($)', 'Gained ($)', 'Net Profit ($)']);

                $data = $this->getDynamicChartFinancialData($range, $start, $end);

                foreach ($data['labels'] as $index => $period) {
                    fputcsv($handle, [
                        $period,
                        $data['spent'][$index],
                        $data['gained'][$index],
                        $data['profit'][$index]
                    ]);
                }
            } elseif ($type === 'suppliers') {
                fputcsv($handle, ['Supplier Name', 'Total Contracts', 'Total Investment ($)', 'Total Revenue ($)', 'Product Diversity']);

                $rawData = $this->getRawSupplierData($start, $end);

                foreach ($rawData as $row) {
                    fputcsv($handle, [
                        $row['supplier_name'],
                        $row['total_contracts'],
                        $row['total_investment'],
                        $row['total_revenue'],
                        $row['product_diversity']
                    ]);
                }
            } elseif ($type === 'occupation') {
                fputcsv($handle, ['Status', 'Total Slots']);

                $data = $this->getOccupationStats();

                fputcsv($handle, ['Occupied', $data['data'][0]]);
                fputcsv($handle, ['Empty', $data['data'][1]]);
            }

            fclose($handle);
        });

        $dateStamp = date('Y-m-d');
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"analytics_{$type}_{$dateStamp}.csv\"");

        return $response;
    }

}
