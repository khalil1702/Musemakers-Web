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
    public function getUserGenderStats(): array
{
    // Initialize an array to hold the statistics
    $stats = [];

    // Count the number of male users
    $stats['homme'] = $this->createQueryBuilder('u')
        ->select('COUNT(u.idUser)')
        ->andWhere('u.sexe = :homme')
        ->setParameter('homme', 'homme')
        ->getQuery()
        ->getSingleScalarResult();

    // Count the number of female users
    $stats['femme'] = $this->createQueryBuilder('u')
        ->select('COUNT(u.idUser)')
        ->andWhere('u.sexe = :femme')
        ->setParameter('femme', 'femme')
        ->getQuery()
        ->getSingleScalarResult();

    return $stats;
}
public function search($nomUser, $prenomUser, $email, $numTel)
{
    $queryBuilder = $this->createQueryBuilder('u');

    if (!empty($nomUser)) {
        $queryBuilder
            ->andWhere('u.nomUser LIKE :nomUser')
            ->setParameter('nomUser', '%' . $nomUser . '%');
    }

    if (!empty($prenomUser)) {
        $queryBuilder
            ->andWhere('u.prenomUser LIKE :prenomUser')
            ->setParameter('prenomUser', '%' . $prenomUser . '%');
    }

    if (!empty($email)) {
        $queryBuilder
            ->andWhere('u.email LIKE :email')
            ->setParameter('email', '%' . $email . '%');
    }

    if (!empty($numTel)) {
        $queryBuilder
            ->andWhere('u.numTel = :numTel')
            ->setParameter('numTel', $numTel);
    }

    return $queryBuilder->getQuery()->getResult();
}





}