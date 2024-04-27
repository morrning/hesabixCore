<?php

namespace App\Repository;

use App\Entity\Person;
use App\Entity\Business;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Person>
 *
 * @method Person|null find($id, $lockMode = null, $lockVersion = null)
 * @method Person|null findOneBy(array $criteria, array $orderBy = null)
 * @method Person[]    findAll()
 * @method Person[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    public function save(Person $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Person $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Person[] Returns an array of Person objects
     */
    public function searchByNikename(Business $bid,string $search,int $maxResults = 10): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.bid = :val')
            ->andWhere("p.nikename LIKE :search")
            ->setParameter('val', $bid)
            ->setParameter('search', '%' . $search . '%')
            ->setMaxResults($maxResults)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Person[] Returns an array of Person objects
     */
    public function getLasts(Business $bid,int $maxResults = 10): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.bid = :val')
            ->setParameter('val', $bid)
            ->setMaxResults($maxResults)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Person[] Returns an array of Person objects
     */
    public function findPlugNoghreEmplyess($bid): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.bid = :val')
            ->orWhere('p.plugNoghreMorsa != TRUE')
            ->orWhere('p.plugNoghreHakak != TRUE')
            ->orWhere('p.plugNoghreTarash != TRUE')
            ->andWhere('p.employe = TRUE')
            ->setParameter('val', $bid)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    public function findOneBySomeField($value): ?Person
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
