<?php

namespace App\Repository;

use App\Entity\SMSPays;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SMSPays>
 *
 * @method SMSPays|null find($id, $lockMode = null, $lockVersion = null)
 * @method SMSPays|null findOneBy(array $criteria, array $orderBy = null)
 * @method SMSPays[]    findAll()
 * @method SMSPays[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SMSPaysRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SMSPays::class);
    }

//    /**
//     * @return SMSPays[] Returns an array of SMSPays objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SMSPays
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
