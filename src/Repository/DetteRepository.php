<?php

namespace App\Repository;

use App\Entity\Dette;
use App\Entity\Client;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class DetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dette::class);
    }

    public function findAllDemandes()
    {
        return $this->createQueryBuilder('d')
            ->innerJoin('d.client', 'c') 
            ->addSelect('c') 
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupérer une dette par son ID
     */
    public function findOneById(int $id): ?Dette
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.client', 'c')
            ->addSelect('c')
            ->where('d.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.statutDemande = :statut')
            ->setParameter('statut', $statut)
            ->getQuery()
            ->getResult();
    }


    public function getTotalDettes(): float
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->select('SUM(d.montantRestant) as totalDettes')
            ->where('d.montantRestant > 0');

        $result = $queryBuilder->getQuery()->getSingleScalarResult();

        return $result !== null ? (float) $result : 0;
    }

    public function countPendingRequests(): int
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.statutDette = :pending')
            ->setParameter('pending', 'Non Soldée');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }


    /**
     * Trouve les dettes non soldées pour un client donné.
     * 
     * @param \App\Entity\Client $client
     * @return \App\Entity\Dette[]
     */
    public function findByNonSoldeeClient(Client $client): array
    {
        $dettes = $this->createQueryBuilder('d')
            ->where('d.client = :client')
            ->andWhere('d.montantRestant > 0')
            ->setParameter('client', $client)
            ->getQuery()
            ->getResult();

        dump($dettes); 
        return $dettes;
    }

    public function findArchived()
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.archived = :val')
            ->setParameter('val', true)
            ->getQuery()
            ->getResult();
    }




}
