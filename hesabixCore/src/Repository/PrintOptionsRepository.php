<?php

namespace App\Repository;

use App\Entity\PrintOptions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrintOptions>
 *
 * @method PrintOptions|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrintOptions|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrintOptions[]    findAll()
 * @method PrintOptions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrintOptionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrintOptions::class);
    }

//    /**
//     * @return PrintOptions[] Returns an array of PrintOptions objects
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

//    public function findOneBySomeField($value): ?PrintOptions
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
