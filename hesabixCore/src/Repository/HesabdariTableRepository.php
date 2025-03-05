<?php

namespace App\Repository;

use App\Entity\HesabdariTable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HesabdariTable>
 *
 * @method HesabdariTable|null find($id, $lockMode = null, $lockVersion = null)
 * @method HesabdariTable|null findOneBy(array $criteria, array $orderBy = null)
 * @method HesabdariTable[]    findAll()
 * @method HesabdariTable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HesabdariTableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HesabdariTable::class);
    }

    public function save(HesabdariTable $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HesabdariTable $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
//     * @return HesabdariTable[] Returns an array of HesabdariTable objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    /**
     * پیدا کردن یک نود با فیلتر bid
     */
    public function findNode($nodeId, $bid): ?HesabdariTable
    {
        return $this->createQueryBuilder('ht')
            ->where('ht.id = :nodeId')
            ->andWhere('ht.bid = :bid OR ht.bid IS NULL') // فقط کسب‌وکار فعلی یا عمومی
            ->setParameter('nodeId', $nodeId)
            ->setParameter('bid', $bid)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
