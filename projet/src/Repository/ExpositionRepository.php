<?php

namespace App\Repository;

use App\Entity\Exposition;
use App\Entity\Oeuvre;
use App\Entity\Reservation;
use App\Entity\Student;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Student>
 *
 * @method Exposition|null find($id, $lockMode = null, $lockVersion = null)
 * @method Exposition|null findOneBy(array $criteria, array $orderBy = null)
 * @method Exposition[]    findAll()
 * @method Exposition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Exposition::class);
    }
    public function searchByNameAndTheme($name, $theme)
    {
        $queryBuilder = $this->createQueryBuilder('e'); 
    
        if (!empty($name)) {
            $queryBuilder
                ->andWhere('e.nom LIKE :name')
                ->setParameter('name', '%' . $name . '%');
        }
    
        if (!empty($theme)) {
            $queryBuilder
                ->andWhere('e.theme = :theme')
                ->setParameter('theme', $theme);
        }
    
        return $queryBuilder->getQuery()->getResult();
    }
   
   
    




//    /**
//     * @return Student[] Returns an array of Student objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Student
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}