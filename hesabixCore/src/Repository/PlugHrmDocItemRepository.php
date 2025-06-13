<?php

namespace App\Repository;

use App\Entity\PlugHrmDocItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PlugHrmDocItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlugHrmDocItem::class);
    }
} 