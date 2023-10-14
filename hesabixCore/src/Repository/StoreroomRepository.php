<?php

namespace App\Repository;

use App\Entity\Storeroom;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Storeroom>
 *
 * @method Storeroom|null find($id, $lockMode = null, $lockVersion = null)
 * @method Storeroom|null findOneBy(array $criteria, array $orderBy = null)
 * @method Storeroom[]    findAll()
 * @method Storeroom[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StoreroomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Storeroom::class);
    }

//    /**
//     * @return Storeroom[] Returns an array of Storeroom objects
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

//    public function findOneBySomeField($value): ?Storeroom
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
