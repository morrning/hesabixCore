<?php

namespace App\Repository;

use App\Entity\PersonType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PersonType>
 *
 * @method PersonType|null find($id, $lockMode = null, $lockVersion = null)
 * @method PersonType|null findOneBy(array $criteria, array $orderBy = null)
 * @method PersonType[]    findAll()
 * @method PersonType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonType::class);
    }

//    /**
//     * @return PersonType[] Returns an array of PersonType objects
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

//    public function findOneBySomeField($value): ?PersonType
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
