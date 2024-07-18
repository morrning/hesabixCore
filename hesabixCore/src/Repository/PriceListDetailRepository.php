<?php

namespace App\Repository;

use App\Entity\PriceListDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PriceListDetail>
 *
 * @method PriceListDetail|null find($id, $lockMode = null, $lockVersion = null)
 * @method PriceListDetail|null findOneBy(array $criteria, array $orderBy = null)
 * @method PriceListDetail[]    findAll()
 * @method PriceListDetail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PriceListDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriceListDetail::class);
    }

//    /**
//     * @return PriceListDetail[] Returns an array of PriceListDetail objects
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

//    public function findOneBySomeField($value): ?PriceListDetail
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
