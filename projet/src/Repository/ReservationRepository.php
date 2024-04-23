<?php

namespace App\Repository;

use App\Entity\Exposition;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 *
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }
    public function getReservationStats(): array
    {
        // Récupérer les statistiques des réservations
        $stats = [];

        // Compter le nombre de réservations pour chaque statut
        $stats['en_cours'] = $this->createQueryBuilder('r')
            ->select('COUNT(r.idReservation)')
            ->andWhere('r.accessByAdmin = 0')
            ->getQuery()
            ->getSingleScalarResult();

        $stats['acceptees'] = $this->createQueryBuilder('r')
            ->select('COUNT(r.idReservation)')
            ->andWhere('r.accessByAdmin = 1')
            ->getQuery()
            ->getSingleScalarResult();

        $stats['annulees'] = $this->createQueryBuilder('r')
            ->select('COUNT(r.idReservation)')
            ->andWhere('r.accessByAdmin = 3')
            ->getQuery()
            ->getSingleScalarResult();

        $stats['refusees'] = $this->createQueryBuilder('r')
            ->select('COUNT(r.idReservation)')
            ->andWhere('r.accessByAdmin = 2')
            ->getQuery()
            ->getSingleScalarResult();

        return $stats;
    }
  
    public function getTopReservedExpositions($limit = 5)
    {
        return $this->createQueryBuilder('r')
            ->select('e.nom AS exposition_nom, COUNT(r.idReservation) AS reservationsCount')
            ->leftJoin('r.exposition', 'e')
            ->where('r.accessByAdmin = :accessByAdmin')
            ->setParameter('accessByAdmin', 1)
            ->groupBy('e.idExposition')
            ->orderBy('reservationsCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    public function findAllSortedByDateDesc(): array
    {
        return $this->createQueryBuilder('r')
        ->andWhere('r.accessByAdmin = :accessByAdmin')
        ->setParameter('accessByAdmin', 0)
        ->orderBy('r.dateReser', 'DESC')
        ->getQuery()
        ->getResult();
}
public function findAllSortedByDateAsc(): array
{
    return $this->createQueryBuilder('r')
        ->andWhere('r.accessByAdmin = :accessByAdmin')
        ->setParameter('accessByAdmin', 0)
        ->orderBy('r.dateReser', 'ASC')
        ->getQuery()
        ->getResult();
}





    }
    
    
    



   
 


