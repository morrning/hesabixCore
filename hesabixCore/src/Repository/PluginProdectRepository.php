<?php

namespace App\Repository;

use App\Entity\PluginProdect;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PluginProdect>
 *
 * @method PluginProdect|null find($id, $lockMode = null, $lockVersion = null)
 * @method PluginProdect|null findOneBy(array $criteria, array $orderBy = null)
 * @method PluginProdect[]    findAll()
 * @method PluginProdect[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PluginProdectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PluginProdect::class);
    }

//    /**
//     * @return PluginProdect[] Returns an array of PluginProdect objects
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

//    public function findOneBySomeField($value): ?PluginProdect
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
