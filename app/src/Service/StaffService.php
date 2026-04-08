<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class StaffService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function promote(User $user) : void
    {
        if($user->getRoles()[0] === 'ROLE_STAFF') {
            $user->setRoles(['ROLE_MANAGER']);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    public function demote(User $user) : void
    {
        if($user->getRoles()[0] === 'ROLE_MANAGER') {
            $user->setRoles(['ROLE_STAFF']);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    public function accept(User $user) : void
    {
        $user->setIsAccepted(true);

        $roles = $user->getRoles();

        $activeRoles = array_map(
            fn ($role) => str_replace('_PENDING', '', $role),
            $roles
        );

        $user->setRoles($activeRoles);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function reject(User $user) : void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
