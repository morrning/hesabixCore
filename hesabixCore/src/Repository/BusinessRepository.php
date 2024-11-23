<?php

namespace App\Repository;

use App\Entity\Business;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Business>
 *
 * @method Business|null find($id, $lockMode = null, $lockVersion = null)
 * @method Business|null findOneBy(array $criteria, array $orderBy = null)
 * @method Business[]    findAll()
 * @method Business[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BusinessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Business::class);
    }

    public function save(Business $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Business $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Business[] Returns an array of Business objects
     */
    public function findByPage($page = 0, $take = 25, $search = ''): array
    {
        $query = $this->createQueryBuilder('b')
            ->setFirstResult(($page -1) * $take)
            ->orderBy('b.id', 'DESC')
            ->setMaxResults($take);
            
        if ($search != '') {
            $query->andWhere("b.name LIKE :search")
            ->setParameter('search', '%' . $search . '%');
        }
        return $query->getQuery()->getResult();
    }

    /**
     * @return  integer Returns an integer of Business objects
     */
    public function countAll(): int
    {
        return $this->createQueryBuilder('b')
            ->select('count(b.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    //    public function findOneBySomeField($value): ?Business
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findLast()
    {
        $res = $this->createQueryBuilder('p')
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
        if (count($res) > 0)
            return $res[count($res) - 1];
        return null;
    }
}
