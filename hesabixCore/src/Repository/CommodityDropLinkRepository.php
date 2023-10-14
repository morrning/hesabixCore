<?php

namespace App\Repository;

use App\Entity\CommodityDropLink;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommodityDropLink>
 *
 * @method CommodityDropLink|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommodityDropLink|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommodityDropLink[]    findAll()
 * @method CommodityDropLink[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommodityDropLinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommodityDropLink::class);
    }

//    /**
//     * @return CommodityDropLink[] Returns an array of CommodityDropLink objects
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

//    public function findOneBySomeField($value): ?CommodityDropLink
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
