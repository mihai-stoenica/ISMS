<?php

namespace App\Service;

use App\Entity\SupplierProfile;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationService
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private EntityManagerInterface $em
    )
    {}

    public function registerStaff(User $user, string $password) : void {
        $user->setPassword($this->hasher->hashPassword($user, $password));
        $user->setRoles(['ROLE_STAFF_PENDING']);
        $user->setIsAccepted(false);

        $this->em->persist($user);
        $this->em->flush();
    }

    public function registerSupplier(SupplierProfile $profile, string $password) : User {
        $user = $profile->getUser();
        $user->setPassword($this->hasher->hashPassword($user, $password));
        $user->setRoles(['ROLE_SUPPLIER_PENDING']);
        $user->setIsAccepted(false);

        $this->em->persist($user);
        $this->em->persist($profile);
        $this->em->flush();

        return $user;
    }
}
