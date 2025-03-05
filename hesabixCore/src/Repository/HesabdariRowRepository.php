<?php

namespace App\Repository;

use App\Entity\HesabdariRow;
use App\Entity\Money;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
     * پیدا کردن ردیف‌ها با جوین روی سند و فیلتر پول، با حذف تکرارها
     */
    public function findByJoinMoney(array $params, Money $money): array
    {
        $query = $this->createQueryBuilder('t')
            ->select('DISTINCT t') // حذف تکرارها با DISTINCT
            ->innerJoin('t.doc', 'd')
            ->where('d.money = :money')
            ->andWhere('t.bid = :bid OR t.bid IS NULL') // فقط کسب‌وکار فعلی یا عمومی
            ->setParameter('money', $money)
            ->setParameter('bid', $params['bid']); // bid از پارامترها دریافت می‌شود

        unset($params['bid']); // حذف bid از params برای جلوگیری از تداخل

        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $temp = array_map(fn($v) => is_object($v) ? $v->getId() : $v, $value);
                $query->andWhere('t.' . $key . ' IN (:' . $key . ')')
                      ->setParameter($key, $temp);
            } else {
                $query->andWhere('t.' . $key . ' = :' . $key)
                      ->setParameter($key, $value);
            }
        }

        return $query->getQuery()->getResult();
    }

     /**
     * @throws \Doctrine\ORM\NonUniqueResultException
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
}