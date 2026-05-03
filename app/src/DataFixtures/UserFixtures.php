<?php

namespace App\DataFixtures;

use App\Entity\SupplierProfile;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $managerUser = new User();
        $managerUser->setEmail('manager@example.com');
        $managerUser->setRoles(['ROLE_MANAGER']);
        $password = $this->hasher->hashPassword($managerUser, 'abc123');
        $managerUser->setPassword($password);
        $managerUser->setIsAccepted(true);
        $managerUser->setName('Manager');

        $manager->persist($managerUser);
        $this->addReference('user-manager', $managerUser);

        $staffUser = new User();
        $staffUser->setEmail('staff@example.com');
        $staffUser->setRoles(['ROLE_STAFF']);
        $staffUser->setName('Worker Bee');
        $staffUser->setIsAccepted(true);
        $staffUser->setPassword($this->hasher->hashPassword($staffUser, 'abc123'));
        $staffUser->setIsAccepted(true);

        $manager->persist($staffUser);
        $this->addReference('user-employee', $staffUser);

        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setEmail("supplier$i@example.com");
            $user->setRoles(['ROLE_SUPPLIER']);
            $user->setIsAccepted(true);
            $user->setName("Supplier Co. $i");
            $user->setPassword($this->hasher->hashPassword($user, 'abc123'));
            $manager->persist($user);

            $profile = new SupplierProfile();
            $profile->setUser($user);
            $profile->setUniqueIdentifier("SUPP-00$i");
            $profile->setAddress($i . " Industrial Way");
            $profile->setPhoneNumber("555-000$i");
            $manager->persist($profile);

            $this->addReference('profile-supplier-' . $i, $profile);
        }

        $manager->flush();
    }
}
