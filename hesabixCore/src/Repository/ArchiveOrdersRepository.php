<?php

namespace App\Repository;

use App\Entity\ArchiveOrders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArchiveOrders>
 *
 * @method ArchiveOrders|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArchiveOrders|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArchiveOrders[]    findAll()
 * @method ArchiveOrders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArchiveOrdersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArchiveOrders::class);
    }

//    /**
//     * @return ArchiveOrders[] Returns an array of ArchiveOrders objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ArchiveOrders
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
