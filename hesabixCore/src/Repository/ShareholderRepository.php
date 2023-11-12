<?php

namespace App\Repository;

use App\Entity\Shareholder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Shareholder>
 *
 * @method Shareholder|null find($id, $lockMode = null, $lockVersion = null)
 * @method Shareholder|null findOneBy(array $criteria, array $orderBy = null)
 * @method Shareholder[]    findAll()
 * @method Shareholder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShareholderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Shareholder::class);
    }

//    /**
//     * @return Shareholder[] Returns an array of Shareholder objects
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

//    public function findOneBySomeField($value): ?Shareholder
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
