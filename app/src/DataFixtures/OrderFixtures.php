<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\ProductOrder;
use App\Entity\SupplierProfile;
use App\Enum\OrderStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 200; $i++) {
            $order = new Order();

            $supplierId = mt_rand(1, 10);
            $supplier = $this->getReference('profile-supplier-' . $supplierId, SupplierProfile::class);
            $order->setSupplier($supplier);

            $isDone = mt_rand(1, 100) <= 90;
            $order->setStatus($isDone ? OrderStatus::DONE : OrderStatus::PENDING);

            if ($isDone) {
                $completedDays = mt_rand(0, 180);
                $order->setCompletedAt(new \DateTimeImmutable("-{$completedDays} days"));
            }

            $manager->persist($order);

            $numberOfItems = mt_rand(1, 5);
            $usedProductIds = [];
            $totalPrice = 0;

            for ($j = 0; $j < $numberOfItems; $j++) {
                $productId = mt_rand(1, 50);

                if (in_array($productId, $usedProductIds)) {
                    continue;
                }
                $usedProductIds[] = $productId;

                $product = $this->getReference('product-' . $productId, Product::class);

                $productOrder = new ProductOrder();
                $productOrder->setOrder($order);
                $productOrder->setProduct($product);

                $qty = mt_rand(1, 20);
                $productOrder->setQuantity($qty);

                $totalPrice += ($product->getSellingPrice() * $qty);

                $manager->persist($productOrder);
            }

            $order->setTotalPrice($totalPrice);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProductFixtures::class,
            UserFixtures::class,
        ];
    }
}
