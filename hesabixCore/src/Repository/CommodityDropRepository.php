<?php

namespace App\Repository;

use App\Entity\CommodityDrop;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommodityDrop>
 *
 * @method CommodityDrop|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommodityDrop|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommodityDrop[]    findAll()
 * @method CommodityDrop[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommodityDropRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommodityDrop::class);
    }

//    /**
//     * @return CommodityDrop[] Returns an array of CommodityDrop objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CommodityDrop
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
