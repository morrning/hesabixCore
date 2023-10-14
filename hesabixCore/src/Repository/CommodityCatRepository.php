<?php

namespace App\Repository;

use App\Entity\CommodityCat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommodityCat>
 *
 * @method CommodityCat|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommodityCat|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommodityCat[]    findAll()
 * @method CommodityCat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommodityCatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommodityCat::class);
    }

//    /**
//     * @return CommodityCat[] Returns an array of CommodityCat objects
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

//    public function findOneBySomeField($value): ?CommodityCat
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
