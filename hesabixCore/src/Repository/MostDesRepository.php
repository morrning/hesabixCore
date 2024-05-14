<?php

namespace App\Repository;

use App\Entity\MostDes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MostDes>
 *
 * @method MostDes|null find($id, $lockMode = null, $lockVersion = null)
 * @method MostDes|null findOneBy(array $criteria, array $orderBy = null)
 * @method MostDes[]    findAll()
 * @method MostDes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MostDesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MostDes::class);
    }

//    /**
//     * @return MostDes[] Returns an array of MostDes objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MostDes
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
