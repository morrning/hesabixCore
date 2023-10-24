<?php

namespace App\Repository;

use App\Entity\Statment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Statment>
 *
 * @method Statment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Statment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Statment[]    findAll()
 * @method Statment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Statment::class);
    }

//    /**
//     * @return Statment[] Returns an array of Statment objects
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

//    public function findOneBySomeField($value): ?Statment
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
