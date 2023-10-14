<?php

namespace App\Repository;

use App\Entity\SMSSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SMSSettings>
 *
 * @method SMSSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method SMSSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method SMSSettings[]    findAll()
 * @method SMSSettings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SMSSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SMSSettings::class);
    }

//    /**
//     * @return SMSSettings[] Returns an array of SMSSettings objects
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

//    public function findOneBySomeField($value): ?SMSSettings
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
