<?php

namespace App\Repository;

use App\Entity\PrinterQueue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrinterQueue>
 *
 * @method PrinterQueue|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrinterQueue|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrinterQueue[]    findAll()
 * @method PrinterQueue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrinterQueueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrinterQueue::class);
    }

//    /**
//     * @return PrinterQueue[] Returns an array of PrinterQueue objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PrinterQueue
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
