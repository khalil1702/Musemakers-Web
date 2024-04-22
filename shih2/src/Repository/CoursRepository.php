<?php

namespace App\Repository;

use App\Entity\Cours; // Importez la classe Cours
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cours>
 *
 * @method Cours|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cours|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cours[]    findAll()
 * @method Cours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cours::class); // Utilisez Cours::class pour le type d'entité
    }
    

    public function findFilteredAndSorted($sortBy, $searchTerm)
    {
        $queryBuilder = $this->createQueryBuilder('c');
    
        if (!empty($searchTerm)) {
            $queryBuilder->andWhere('c.titreCours LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }
    
        if ($sortBy === 'default_field') {
            // Utiliser un champ de tri par défaut
            $sortBy = 'idCours'; // Par exemple, trier par ID
        }
    
        return $queryBuilder
            ->orderBy('c.' . $sortBy, 'ASC')
            ->getQuery()
            ->getResult();
    }
   
}