<?php

namespace App\Repository;

use App\Entity\PrintItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrintItem>
 *
 * @method PrintItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrintItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrintItem[]    findAll()
 * @method PrintItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrintItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrintItem::class);
    }

//    /**
//     * @return PrintItem[] Returns an array of PrintItem objects
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

//    public function findOneBySomeField($value): ?PrintItem
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
