<?php

namespace App\Repository;

use App\Entity\PersonCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PersonCard>
 *
 * @method PersonCard|null find($id, $lockMode = null, $lockVersion = null)
 * @method PersonCard|null findOneBy(array $criteria, array $orderBy = null)
 * @method PersonCard[]    findAll()
 * @method PersonCard[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonCardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonCard::class);
    }

//    /**
//     * @return PersonCard[] Returns an array of PersonCard objects
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

//    public function findOneBySomeField($value): ?PersonCard
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
