<?php

namespace App\DataFixtures;

use App\Entity\Contract;
use App\Entity\Product;
use App\Entity\SupplierProfile;
use App\Entity\User;
use App\Enum\ContractStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Enum\Location;

class ContractFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $managerUser = $this->getReference('user-manager', User::class);

        for ($i = 1; $i <= 100; $i++) {
            $contract = new Contract();

            $supplierId = mt_rand(1, 10);
            $supplier = $this->getReference('profile-supplier-' . $supplierId, SupplierProfile::class);
            $contract->setSupplier($supplier);

            $contract->setManager($managerUser);

            $productId = mt_rand(1, 50);
            $product = $this->getReference('product-' . $productId, Product::class);
            $contract->setProduct($product);

            $contract->setQuantity(mt_rand(50, 1000));

            $contract->setRamp(Location::R1);

            $status = (mt_rand(1, 100) <= 80) ? ContractStatus::DONE : ContractStatus::PENDING;
            $contract->setStatus($status);

            $contract->setTotalCost((float) mt_rand(1000, 50000));

            $daysAgo = mt_rand(0, 180);
            $date = new \DateTime("-{$daysAgo} days");
            $contract->setDate($date);

            $manager->persist($contract);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ProductFixtures::class,
        ];
    }
}
