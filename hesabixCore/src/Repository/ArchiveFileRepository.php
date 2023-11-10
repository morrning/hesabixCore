<?php

namespace App\Repository;

use App\Entity\ArchiveFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArchiveFile>
 *
 * @method ArchiveFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArchiveFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArchiveFile[]    findAll()
 * @method ArchiveFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArchiveFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArchiveFile::class);
    }

//    /**
//     * @return ArchiveFile[] Returns an array of ArchiveFile objects
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

//    public function findOneBySomeField($value): ?ArchiveFile
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
