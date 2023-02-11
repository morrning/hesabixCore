<?php

namespace App\Repository;

use App\Entity\StackContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StackContent>
 *
 * @method StackContent|null find($id, $lockMode = null, $lockVersion = null)
 * @method StackContent|null findOneBy(array $criteria, array $orderBy = null)
 * @method StackContent[]    findAll()
 * @method StackContent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StackContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StackContent::class);
    }

    public function save(StackContent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StackContent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /*
     * @return StackContent[]
     */
    public function search($params): array
    {
        return $this->createQueryBuilder('s')
            ->setMaxResults($params['count'])
            ->setFirstResult(($params['page'] -1) * $params['count'])
            ->orWhere('s.body LIKE :key')
            ->andWhere('s.upper is NULL')
            ->orderBy('s.id', 'DESC')
            ->setParameter('key','%' . $params['key'] . '%')
            ->getQuery()
            ->getResult()
        ;
   }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getStackCount(): int{
       return $this->createQueryBuilder('s')
           ->select('count(s.id)')
           ->andWhere('s.upper is NULL')
           ->getQuery()
           ->getSingleScalarResult()
           ;
   }
    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getAllReplayCount(): int{
        return $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->andWhere('s.upper IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getCountReplaysOfQuestion(StackContent $upper): int{
        return $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->andWhere('s.upper = :upper')
            ->setParameter('upper' , $upper)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getReplaysOfQuestion(StackContent $upper): array{
        return $this->createQueryBuilder('s')
            ->select('s')
            ->andWhere('s.upper = :upper')
            ->setParameter('upper' , $upper)
            ->getQuery()
            ->getResult()
            ;
    }

}
