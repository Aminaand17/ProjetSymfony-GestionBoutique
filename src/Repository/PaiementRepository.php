<?php

namespace App\Repository;

use App\Entity\Paiement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Paiement>
 *
 * @method Paiement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Paiement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Paiement[]    findAll()
 * @method Paiement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Paiement::class);
    }

    /**
     * Persist a Paiement entity.
     */
    public function save(Paiement $paiement, bool $flush = true): void
    {
        $this->_em->persist($paiement);

        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Remove a Paiement entity.
     */
    public function remove(Paiement $paiement, bool $flush = true): void
    {
        $this->_em->remove($paiement);

        if ($flush) {
            $this->_em->flush();
        }
    }

}
