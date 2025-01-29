<?php

namespace App\Repository;

use App\Entity\Commodity;
use App\Entity\HesabdariRow;
use App\Entity\Money;
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


    /**
     * @throws NonUniqueResultException
     */
    public function getNotEqual($doc, $notField): ?HesabdariRow
    {
        $qb = $this->createQueryBuilder('t');
        return $qb->select('t')
            ->where($qb->expr()->isNotNull('t.' . $notField))
            ->andWhere('t.doc = :doc')
            ->setParameter('doc', $doc)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByJoinMoney(array $params, Money $money): array
    {
        $query = $this->createQueryBuilder('t')
            ->select('t')
            ->innerJoin('t.doc', 'd')
            ->where('d.money = :money')
            ->setParameter('money', $money);
        foreach ($params as $key => $value) {
            $query->andWhere('t.' . $key . '= :' . $key);
            $query->setParameter($key, $value);
        }
        return $query->getQuery()->getResult();
    }
}
