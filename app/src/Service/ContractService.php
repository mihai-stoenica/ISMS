<?php

namespace App\Service;

use App\Entity\Contract;
use App\Entity\User;
use App\Enum\ContractStatus;
use App\Enum\Location;
use App\Repository\ProductRepository;
use App\Repository\SupplierProfileRepository;
use Doctrine\ORM\EntityManagerInterface;


class ContractService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SupplierProfileRepository $supplierRepo,
        private ProductRepository $productRepo
    ) {}

    public function createContract(User $manager, int $supplierId, int $productId, int $quantity, Location $ramp): array
    {
        $supplier = $this->supplierRepo->find($supplierId);
        $product = $this->productRepo->find($productId);

        if (!$supplier || !$product) {
            return ['success' => false, 'message' => 'The supplier or product could not be found.'];
        }

        if ($quantity <= 0) {
            return ['success' => false, 'message' => 'Please enter a valid quantity greater than zero.'];
        }

        $contract = new Contract();
        $contract->setManager($manager);
        $contract->setSupplier($supplier);
        $contract->setProduct($product);
        $contract->setQuantity($quantity);


        $contract->setRamp($ramp);

        $contract->setDate(new \DateTime());
        $contract->setStatus(ContractStatus::PENDING);

        $totalCost = $product->getSellingPrice() * $quantity;
        $maxAllowedCost = 2147483647;
        if ($totalCost > $maxAllowedCost) {
            return ['success' => false, 'message' => 'This value is too large. Please reduce the quantity.'];
        }
        $contract->setTotalCost((string) $totalCost);

        $this->entityManager->persist($contract);
        $this->entityManager->flush();

        return ['success' => true, 'message' => "Contract successfully generated for {$quantity}x {$product->getName()} at Ramp {$ramp->value}!"];
    }
    public function updateContractStatus(int $contractId, User $user, ContractStatus $newStatus): array
    {
        $contract = $this->entityManager->getRepository(Contract::class)->find($contractId);

        if (!$contract) {
            return ['success' => false, 'message' => 'Contract not found.'];
        }

        $supplierProfile = $user->getSupplierProfile();

        if (!$supplierProfile || $contract->getSupplier() !== $supplierProfile) {
            return ['success' => false, 'message' => 'Unauthorized: You can only modify your own contracts.'];
        }

        if ($contract->getStatus() !== ContractStatus::PENDING) {
            return ['success' => false, 'message' => 'This contract has already been processed.'];
        }

        if ($newStatus === ContractStatus::DONE) {
            $product = $contract->getProduct();
            $quantityPurchased = $contract->getQuantity();
            $currentStock = $product->getCurrentStock() ?? 0;

            if ($currentStock > 0) {
                $product->setCurrentStock($currentStock + $quantityPurchased);
            } else {
                $product->setCurrentStock($quantityPurchased);

                $ramp = $contract->getRamp();
                if ($ramp) {
                    $product->setLocation($ramp);
                }
            }
        }

        $contract->setStatus($newStatus);

        $this->entityManager->flush();

        $statusName = $newStatus->name;
        return ['success' => true, 'message' => "Contract successfully $statusName. Inventory and routing updated."];
    }
}
