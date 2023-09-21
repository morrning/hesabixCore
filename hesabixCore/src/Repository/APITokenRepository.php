<?php

namespace App\Repository;

use App\Entity\APIToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<APIToken>
 *
 * @method APIToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method APIToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method APIToken[]    findAll()
 * @method APIToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class APITokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, APIToken::class);
    }

//    /**
//     * @return APIToken[] Returns an array of APIToken objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?APIToken
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
