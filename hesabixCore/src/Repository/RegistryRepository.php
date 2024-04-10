<?php

namespace App\Repository;

use App\Entity\Registry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Registry>
 *
 * @method Registry|null find($id, $lockMode = null, $lockVersion = null)
 * @method Registry|null findOneBy(array $criteria, array $orderBy = null)
 * @method Registry[]    findAll()
 * @method Registry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegistryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Registry::class);
    }

//    /**
//     * @return Registry[] Returns an array of Registry objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Registry
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
