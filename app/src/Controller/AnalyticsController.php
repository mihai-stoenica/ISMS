<?php

namespace App\Controller;

use App\Service\AnalyticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AnalyticsController extends AbstractController
{
    #[Route('/analytics/{type}', name: 'app_analytics')]
    #[IsGranted('ROLE_MANAGER')]
    public function index(
        Request $request,
        AnalyticsService $service,
        string $type,
    ): Response
    {
        $range = $request->query->get('range', 'month');
        $start = $request->query->get('start');
        $end = $request->query->get('end');

        $result = $service->createChartByType($type, $range, $start, $end);

        return $this->render('analytics/index.html.twig', [
            'chart' => $result['chart'],
            'currentRange' => $range,
            'extraData' => $extraData ?? null,
            'currentType' => $type,
        ]);
    }
}
