<?php

namespace App\Repository;

use App\Entity\PrintTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrintTemplate>
 *
 * @method PrintTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrintTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrintTemplate[]    findAll()
 * @method PrintTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrintTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrintTemplate::class);
    }

//    /**
//     * @return PrintTemplate[] Returns an array of PrintTemplate objects
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

//    public function findOneBySomeField($value): ?PrintTemplate
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
