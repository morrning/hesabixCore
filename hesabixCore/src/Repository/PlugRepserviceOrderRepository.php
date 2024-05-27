<?php

namespace App\Repository;

use App\Entity\PlugRepserviceOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlugRepserviceOrder>
 *
 * @method PlugRepserviceOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlugRepserviceOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlugRepserviceOrder[]    findAll()
 * @method PlugRepserviceOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlugRepserviceOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlugRepserviceOrder::class);
    }

//    /**
//     * @return PlugRepserviceOrder[] Returns an array of PlugRepserviceOrder objects
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

//    public function findOneBySomeField($value): ?PlugRepserviceOrder
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
