<?php

namespace App\Repository;

use App\Entity\Business;
use App\Entity\WalletTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WalletTransaction>
 *
 * @method WalletTransaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method WalletTransaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method WalletTransaction[]    findAll()
 * @method WalletTransaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WalletTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WalletTransaction::class);
    }

//    /**
//     * @return WalletTransaction[] Returns an array of WalletTransaction objects
//     */
    public function findAllIncome(Business $business): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.bid = :val')
            ->andWhere("w.type != 'pay'")
            ->setParameter('val', $business)
            ->orderBy('w.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

//    public function findOneBySomeField($value): ?WalletTransaction
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
