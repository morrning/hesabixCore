<?php

namespace App\Repository;

use App\Entity\StoreroomTicket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StoreroomTicket>
 *
 * @method StoreroomTicket|null find($id, $lockMode = null, $lockVersion = null)
 * @method StoreroomTicket|null findOneBy(array $criteria, array $orderBy = null)
 * @method StoreroomTicket[]    findAll()
 * @method StoreroomTicket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StoreroomTicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StoreroomTicket::class);
    }

//    /**
//     * @return StoreroomTicket[] Returns an array of StoreroomTicket objects
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

//    public function findOneBySomeField($value): ?StoreroomTicket
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
