<?php

namespace App\Repository;

use App\Entity\PlugRepserviceOrderState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlugRepserviceOrderState>
 *
 * @method PlugRepserviceOrderState|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlugRepserviceOrderState|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlugRepserviceOrderState[]    findAll()
 * @method PlugRepserviceOrderState[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlugRepserviceOrderStateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlugRepserviceOrderState::class);
    }

//    /**
//     * @return PlugRepserviceOrderState[] Returns an array of PlugRepserviceOrderState objects
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

//    public function findOneBySomeField($value): ?PlugRepserviceOrderState
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
