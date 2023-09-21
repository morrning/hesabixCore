<?php

namespace App\Repository;

use App\Entity\PlugNoghreOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlugNoghreOrder>
 *
 * @method PlugNoghreOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlugNoghreOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlugNoghreOrder[]    findAll()
 * @method PlugNoghreOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlugNoghreOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlugNoghreOrder::class);
    }

//    /**
//     * @return PlugNoghreOrder[] Returns an array of PlugNoghreOrder objects
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

//    public function findOneBySomeField($value): ?PlugNoghreOrder
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
