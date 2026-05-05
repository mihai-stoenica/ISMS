<?php

namespace App\Controller;

use App\Service\AnalyticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

final class AnalyticsController extends AbstractController
{
    #[Route('/analytics/{type}', name: 'app_analytics')]
    #[IsGranted('ROLE_MANAGER')]
    public function index(
        Request $request,
        AnalyticsService $service,
        ChartBuilderInterface $cb,
        string $type,
    ): Response
    {
        $range = $request->query->get('range', 'month');
        $start = $request->query->get('start');
        $end = $request->query->get('end');

        $chart = null;

        if($type == 'financial'){
            $chart = $cb->createChart(Chart::TYPE_LINE);

            $financialData = $service->getDynamicChartFinancialData($range, $start, $end);

            $chart->setData([
                'labels' => $financialData['labels'],
                'datasets' => [
                    [
                        'label' => 'Sums Spent',
                        'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                        'borderColor' => 'rgb(54, 162, 235)',
                        'data' => $financialData['spent'],
                        'tension' => 0.4
                    ],
                    [
                        'label' => 'Sums Gained',
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        'borderColor' => 'rgb(75, 192, 192)',
                        'data' => $financialData['gained'],
                        'tension' => 0.4
                    ],
                    [
                        'label' => 'Net Profit',
                        'borderColor' => 'rgb(153, 102, 255)',
                        'data' => $financialData['profit'],
                        'tension' => 0.4,
                        'fill' => false
                    ]
                ],
            ]);
        } else if($type == 'occupation'){
            $data = $service->getOccupationStats();

            $chart = $cb->createChart(Chart::TYPE_DOUGHNUT);

            $chart->setData([
                'labels' => $data['labels'],
                'datasets' => [[
                    'backgroundColor' => ['#ff6384', '#36a2eb'],
                    'data' => $data['data'],
                ]],
            ]);

            $extraData = ['percent' => $data['percentage'], 'total' => $data['total']];
        }

        $chart->setOptions([
            'maintainAspectRatio' => false,
            'responsive' => true,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ]
            ]
        ]);

        return $this->render('analytics/index.html.twig', [
            'chart' => $chart,
            'currentRange' => $range,
            'extraData' => $extraData ?? null,
            'currentType' => $type,
        ]);
    }
}
