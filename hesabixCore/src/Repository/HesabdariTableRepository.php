<?php

namespace App\Repository;

use App\Entity\HesabdariTable;
use App\Entity\Business;
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

    /**
     * Find all sub-account codes recursively, filtered by business ID.
     *
     * @param string $accountCode The account code to start with
     * @param int|Business|null $business The business ID or Business entity (or null for no business filter)
     * @return array List of unique account codes
     */
    public function findAllSubAccountCodes(string $accountCode, $business): array
    {
        $businessId = $business instanceof Business ? $business->getId() : $business;
        $result = [$accountCode];

        // Find the parent node by account code and business filter
        $qb = $this->createQueryBuilder('t')
            ->select('t.id')
            ->where('t.code = :code')
            ->setParameter('code', $accountCode);

        // Apply business filter
        if ($businessId !== null) {
            $qb->andWhere('t.bid IS NULL OR t.bid = :businessId')
                ->setParameter('businessId', $businessId);
        }

        $parent = $qb->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$parent) {
            return array_unique($result);
        }

        // Find direct children using the upper relationship and business filter
        $qb = $this->createQueryBuilder('t')
            ->select('t.code')
            ->where('t.upper = :parentId')
            ->setParameter('parentId', $parent['id']);

        // Apply business filter for children
        if ($businessId !== null) {
            $qb->andWhere('t.bid IS NULL OR t.bid = :businessId')
                ->setParameter('businessId', $businessId);
        }

        $subAccounts = $qb->getQuery()->getResult();

        // Recursively find sub-accounts for each child
        foreach ($subAccounts as $subAccount) {
            $result = array_merge($result, $this->findAllSubAccountCodes($subAccount['code'], $businessId));
        }

        return array_unique($result);
    }
}
