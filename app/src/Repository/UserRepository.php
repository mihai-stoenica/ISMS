<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findBySearchParams(?string $status, ?string $type, ?string $search): Query
    {
        $queryBuilder = $this->createQueryBuilder('u');

        if($status){
            $isActive = ($status == 'active');
            $queryBuilder
                ->andWhere('u.is_accepted = :acceptedStatus')
                ->setParameter('acceptedStatus', $isActive);
        }

        if($type){
            $queryBuilder
                ->andWhere('u.roles LIKE :role')
                ->setParameter('role', '%'.strtoupper($type).'%');
        }

        if($search){
            $queryBuilder
                ->andWhere('u.name LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }
        return $queryBuilder->getQuery();
    }
    /**
     * @return User[] Returns an array of Users with the Manager role
     */
    public function findManagers(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%"ROLE_MANAGER"%')
            ->getQuery()
            ->getResult();
    }
}
