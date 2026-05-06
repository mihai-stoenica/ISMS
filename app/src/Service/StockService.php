<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class StockService
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductRepository $productRepository,
    ) {}

    /**
     * Updates the low stock threshold for a given product.
     *
     * @return array{success: bool, message: string}
     */
    #[Route('/threshold/{id}', name: 'app_stock_update_threshold', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function updateThreshold(Product $product, int $newThreshold): array
    {
        if ($newThreshold < 0) {
            return [
                'success' => false,
                'message' => 'Threshold cannot be a negative number.'
            ];
        }

        $product->setLowStockThreshold($newThreshold);

        $this->entityManager->flush();

        return [
            'success' => true,
            'message' => "Alert threshold successfully updated for {$product->getName()}."
        ];
    }
    public function generateInventoryCsv(): string
    {
        $products = $this->productRepository->findAll();

        $handle = fopen('php://temp', 'r+');

        fputs($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'ID',
            'Product Name',
            'Category',
            'Location',
            'Current Stock',
            'Alert Threshold',
            'Selling Price ($)'
        ]);

        foreach ($products as $product) {
            fputcsv($handle, [
                $product->getId(),
                $product->getName(),
                $product->getCategory() ? $product->getCategory()->getName() : 'Uncategorized',
                $product->getLocation() ? $product->getLocation()->value : 'Unassigned',
                $product->getCurrentStock() ?? 0,
                $product->getLowStockThreshold(),
                $product->getSellingPrice()
            ]);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return $csvContent;
    }
}
