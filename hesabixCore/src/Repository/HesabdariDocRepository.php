<?php

namespace App\Repository;

use App\Entity\HesabdariDoc;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HesabdariDoc>
 *
 * @method HesabdariDoc|null find($id, $lockMode = null, $lockVersion = null)
 * @method HesabdariDoc|null findOneBy(array $criteria, array $orderBy = null)
 * @method HesabdariDoc[]    findAll()
 * @method HesabdariDoc[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HesabdariDocRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HesabdariDoc::class);
    }

    public function save(HesabdariDoc $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HesabdariDoc $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return HesabdariDoc[] Returns an array of HesabdariDoc objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?HesabdariDoc
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
