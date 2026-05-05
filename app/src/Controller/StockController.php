<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\StockService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/stocks')]
final class StockController extends AbstractController
{
    #[Route('/', name: 'app_stock_index', methods: ['GET'])]
    public function index(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $search = $request->query->get('search');
        $categoryId = $request->query->get('categoryId');

        $query = $productRepository->findBySearch($search, $categoryId);
        $categories = $categoryRepository->findAll();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            7
        );

        return $this->render('stock/index.html.twig', [
            'products' => $pagination,
            'categories' => $categories,
        ]);
    }

    #[Route('/{id}/threshold', name: 'app_stock_update_threshold', methods: ['POST'])]
    #[IsGranted("ROLE_MANAGER")]
    public function updateThreshold(
        Product $product,
        Request $request,
        StockService $stockService
    ): Response {
        $newThreshold = $request->request->getInt('threshold', 100);

        $result = $stockService->updateThreshold($product, $newThreshold);

        if (!$result['success']) {
            $this->addFlash('error', $result['message']);
        } else {
            $this->addFlash('success', $result['message']);
        }

        return $this->redirectToRoute('app_stock_index');
    }
    #[Route('/export', name: 'app_stock_export', methods: ['GET'])]
    #[IsGranted("ROLE_MANAGER")]
    public function exportCsv(StockService $stockService): Response
    {
        $csvContent = $stockService->generateInventoryCsv();

        $response = new Response($csvContent);

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="warehouse_inventory.csv"');

        return $response;
    }
}
