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
            ->andWhere('r.accessByAdmin = 2')
            ->getQuery()
            ->getSingleScalarResult();

        $stats['refusees'] = $this->createQueryBuilder('r')
            ->select('COUNT(r.idReservation)')
            ->andWhere('r.accessByAdmin = 3')
            ->getQuery()
            ->getSingleScalarResult();

        return $stats;
    }
  
    public function getTopReservedExpositions(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.exposition', 'COUNT(r.idReservation) as reservationsCount')
            ->join('r.exposition', 'exposition') // Join with the Exposition entity
            ->andWhere('r.accessByAdmin = 1')
            ->groupBy('exposition') // Group by the Exposition entity
            ->orderBy('reservationsCount', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }
    
    
    



   
 
}

