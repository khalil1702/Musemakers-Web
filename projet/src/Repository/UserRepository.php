<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
    public function searchByNomAndPrenom(string $nom, string $prenom): array
    {
        $queryBuilder = $this->createQueryBuilder('u');

        if (!empty($nom)) {
            $queryBuilder
                ->andWhere('u.nomUser LIKE :nom')
                ->setParameter('nom', '%' . $nom . '%');
        }

        if (!empty($prenom)) {
            $queryBuilder
                ->andWhere('u.prenomUser LIKE :prenom')
                ->setParameter('prenom', '%' . $prenom . '%');
        }

        return $queryBuilder->getQuery()->getResult();
    }
}

