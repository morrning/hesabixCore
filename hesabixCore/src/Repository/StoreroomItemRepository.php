<?php

namespace App\Repository;

use App\Entity\StoreroomItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StoreroomItem>
 *
 * @method StoreroomItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method StoreroomItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method StoreroomItem[]    findAll()
 * @method StoreroomItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StoreroomItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StoreroomItem::class);
    }

//    /**
//     * @return StoreroomItem[] Returns an array of StoreroomItem objects
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

//    public function findOneBySomeField($value): ?StoreroomItem
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
