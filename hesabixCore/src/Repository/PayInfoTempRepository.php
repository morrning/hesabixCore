<?php

namespace App\Repository;

use App\Entity\PayInfoTemp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PayInfoTemp>
 *
 * @method PayInfoTemp|null find($id, $lockMode = null, $lockVersion = null)
 * @method PayInfoTemp|null findOneBy(array $criteria, array $orderBy = null)
 * @method PayInfoTemp[]    findAll()
 * @method PayInfoTemp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PayInfoTempRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PayInfoTemp::class);
    }

//    /**
//     * @return PayInfoTemp[] Returns an array of PayInfoTemp objects
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

//    public function findOneBySomeField($value): ?PayInfoTemp
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
