<?php

namespace App\Repository;

use App\Entity\Plugin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Plugin>
 *
 * @method Plugin|null find($id, $lockMode = null, $lockVersion = null)
 * @method Plugin|null findOneBy(array $criteria, array $orderBy = null)
 * @method Plugin[]    findAll()
 * @method Plugin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PluginRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plugin::class);
    }

    /**
     * @return Plugin[] Returns an array of Plugin objects
     */
    public function findActivePlugins($bid): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.bid = :val')
            ->andWhere('p.dateExpire > :timer')
            ->andWhere('p.refID IS NOT NULL')
            ->setParameter('val', $bid)
            ->setParameter('timer', time())
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

//    public function findOneBySomeField($value): ?Plugin
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
