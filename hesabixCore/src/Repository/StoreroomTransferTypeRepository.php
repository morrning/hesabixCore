<?php

namespace App\Repository;

use App\Entity\StoreroomTransferType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StoreroomTransferType>
 *
 * @method StoreroomTransferType|null find($id, $lockMode = null, $lockVersion = null)
 * @method StoreroomTransferType|null findOneBy(array $criteria, array $orderBy = null)
 * @method StoreroomTransferType[]    findAll()
 * @method StoreroomTransferType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StoreroomTransferTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StoreroomTransferType::class);
    }

//    /**
//     * @return StoreroomTransferType[] Returns an array of StoreroomTransferType objects
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

//    public function findOneBySomeField($value): ?StoreroomTransferType
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
