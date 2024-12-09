<?php

namespace App\Repository;

use App\Entity\Business;
use App\Entity\Commodity;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Commodity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commodity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commodity[]    findAll()
 * @method Commodity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommodityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commodity::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Commodity $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Commodity $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
    public function getListAll($bid)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.bid = :val')
            ->setParameter('val', $bid)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    /**
     * @return Commodity[] Returns an array of Commodity objects
     */
    public function searchByName(Business $bid, string $search, int $maxResults = 10): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.bid = :val')
            ->andWhere("p.name LIKE :search OR p.barcodes LIKE :search")
            ->setParameter('val', $bid)
            ->setParameter('search', '%' . $search . '%')
            ->setMaxResults($maxResults)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Commodity[] Returns an array of Commodity objects
     */
    public function searchBarcode(Business $bid, string $search): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.bid = :val')
            ->andWhere("p.barcodes LIKE :search")
            ->setParameter('val', $bid)
            ->setParameter('search', '%' . $search . '%')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Commodity[] Returns an array of Commodity objects
     */
    public function getLasts(Business $bid, int $maxResults = 10): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.bid = :val')
            ->setParameter('val', $bid)
            ->setMaxResults($maxResults)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
    // /**
    //  * @return Commodity[] Returns an array of Commodity objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Commodity
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @return Commodity[] Returns an array of Commodity objects
     */
    public function search(array $params): array
    {
        $query = $this->createQueryBuilder('p');
        $query->where('p.bid = :val')
            ->setParameter('val', $params['bid']);
        foreach ($params['Filters'] as $filter) {
            if ($filter['Operator'] == '=') {
                $query->andWhere('p.' . $filter['Property'] . '=:' . $filter['Property'])
                    ->setParameter($filter['Property'], $filter['Value']);
            }
        }

        $query->setMaxResults($params['Take'])
            ->orderBy('p.id', 'ASC');

        return $query->getQuery()->getResult();
    }
}
