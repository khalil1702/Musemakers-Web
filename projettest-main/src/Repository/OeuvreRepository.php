<?php

namespace App\Repository;

use App\Entity\Oeuvre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Oeuvre>
 *
 * @method Oeuvre|null find($id, $lockMode = null, $lockVersion = null)
 * @method  Oeuvre|null findOneBy(array $criteria, array $orderBy = null)
 * @method  Oeuvre[]    findAll()
 * @method  Oeuvre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

class OeuvreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Oeuvre::class);
    }

    // Your other methods...

    public function searchByName($query)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.nomOeuvre LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }
}