<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\SupplierProduct;
use App\Entity\SupplierProfile;
use App\Entity\Task;
use App\Entity\User;
use App\Enum\Location;
use App\Enum\TaskStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $managerUser = $this->getReference('user-manager', User::class);
        $employeeUser = $this->getReference('user-employee', User::class);
        $suppliers = [
            $this->getReference('profile-supplier-1', SupplierProfile::class),
            $this->getReference('profile-supplier-2', SupplierProfile::class),
            $this->getReference('profile-supplier-3', SupplierProfile::class),
            $this->getReference('profile-supplier-4', SupplierProfile::class),
            $this->getReference('profile-supplier-5', SupplierProfile::class),
            $this->getReference('profile-supplier-6', SupplierProfile::class),
            $this->getReference('profile-supplier-7', SupplierProfile::class),
            $this->getReference('profile-supplier-8', SupplierProfile::class),
            $this->getReference('profile-supplier-9', SupplierProfile::class),
            $this->getReference('profile-supplier-10', SupplierProfile::class),
        ];

        $categories = [];
        $catNames = ['Electronics', 'Home & Garden', 'Office Supplies'];

        foreach ($catNames as $name) {
            $category = new Category();
            $category->setName($name);
            $manager->persist($category);
            $categories[] = $category;
        }

        $allLocations = array_filter(Location::cases(), fn($loc) => !$loc->isRamp());
        shuffle($allLocations);

        $occupiedLocations = [];
        $products = [];

        for ($i = 1; $i <= 50; $i++) {
            $product = new Product();
            $product->setName("Industrial Component #" . str_pad($i, 3, '0', STR_PAD_LEFT));

            $sellingPrice = (float) mt_rand(100, 1000);
            $product->setSellingPrice($sellingPrice);

            $product->setCurrentStock(mt_rand(0, 500));
            $product->setLowStockThreshold(50);

            $product->setCategory($categories[array_rand($categories)]);

            $loc = array_pop($allLocations);
            $product->setLocation($loc);
            $occupiedLocations[$loc->value] = true;

            $manager->persist($product);
            $products[] = $product;

            $this->addReference('product-' . $i, $product);

            $randomKeys = array_rand($suppliers, mt_rand(1, 10));

            $assignedSuppliers = is_array($randomKeys) ? $randomKeys : [$randomKeys];

            foreach ($assignedSuppliers as $key) {
                $supplier = $suppliers[$key];

                $sp = new SupplierProduct();
                $sp->setProduct($product);
                $sp->setSupplier($supplier);

                $variance = mt_rand(90, 110) / 100;
                $sp->setPurchasePrice($product->getSellingPrice() * 0.7 * $variance);

                $manager->persist($sp);
            }
        }

        foreach ($products as $index => $prod) {
            if (($index + 1) % 5 === 0) {
                $task = new Task();
                $task->setProduct($prod);
                $task->setManager($managerUser);
                $task->setEmployee($employeeUser);
                $task->setStatus(TaskStatus::PENDING);
                $task->setCreatedAt(new \DateTimeImmutable());
                $task->setSource($prod->getLocation());

                if (!empty($allLocations)) {
                    $dest = array_pop($allLocations);
                    $task->setDestination($dest);
                } else {
                    $task->setDestination(Location::R1);
                }

                $manager->persist($task);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
