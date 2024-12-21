<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Persists an Article entity.
     *
     * @param Article $entity
     * @param bool $flush
     */
    public function save(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Removes an Article entity.
     *
     * @param Article $entity
     * @param bool $flush
     */
    public function remove(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Finds all articles that are out of stock (qteStock = 0).
     *
     * @return Article[]
     */
    public function findOutOfStock(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.qteStock = 0')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds articles with stock less than a specified threshold.
     *
     * @param int $threshold
     * @return Article[]
     */
    public function findLowStock(int $threshold = 10): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.qteStock < :threshold')
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds articles by keyword in their libelle.
     *
     * @param string $keyword
     * @return Article[]
     */
    public function searchByKeyword(string $keyword): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.libelle LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->getQuery()
            ->getResult();
    }


    public function findFilteredArticles(string $filter, string $search): array
    {
        $qb = $this->createQueryBuilder('a');

        if (!empty($search)) {
            $qb->andWhere('a.libelle LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($filter === 'disponible') {
            $qb->andWhere('a.qteStock > 0');
        } elseif ($filter === 'rupture') {
            $qb->andWhere('a.qteStock = 0');
        }

        return $qb->orderBy('a.libelle', 'ASC')->getQuery()->getResult();
    }

    public function countInStock(): int
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.qteStock > 0');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }


    public function findRuptureStock(int $limit, int $offset): array
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->where('a.qteStock = 0')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }
}


