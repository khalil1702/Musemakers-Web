<?php

namespace App\Repository;

use App\Entity\User; // Assurez-vous d'importer la classe User correctement
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
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
        parent::__construct($registry, User::class); // Utilisez User::class pour le type d'entité
    }


    public function findLimitedUsers($limit): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.idUser', 'ASC')
            ->setMaxResults($limit) 
            ->getQuery()
            ->getResult();
    }
    // Ajoutez des méthodes personnalisées si nécessaire
}
