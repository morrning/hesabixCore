<?php

namespace App\Repository;

use App\Entity\HesabdariRow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HesabdariRow>
 *
 * @method HesabdariRow|null find($id, $lockMode = null, $lockVersion = null)
 * @method HesabdariRow|null findOneBy(array $criteria, array $orderBy = null)
 * @method HesabdariRow[]    findAll()
 * @method HesabdariRow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HesabdariRowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HesabdariRow::class);
    }

    public function save(HesabdariRow $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HesabdariRow $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return HesabdariRow[] Returns an array of HesabdariRow objects
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

    /**
     * @throws NonUniqueResultException
     */
    public function getNotEqual($doc, $notField): ?HesabdariRow
    {
        $qb = $this->createQueryBuilder('t');
        return $qb->select('t')
            ->where($qb->expr()->isNotNull('t.' . $notField))
            ->andWhere('t.doc = :doc')
            ->setParameter('doc',$doc)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
