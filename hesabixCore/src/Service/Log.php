<?php

namespace App\Service;

use App\Entity\Business;
use Doctrine\ORM\EntityManagerInterface;

class Log
{

    private $em;

    function __construct(EntityManagerInterface  $entityManager)
    {
        $this->em = $entityManager;
    }

    public function insert($part,$des, $user = null, $bid = null): void
    {
        if(is_string($bid))
            $bid = $this->em->getRepository(Business::class)->find($bid);
        $log = new \App\Entity\Log();
        $log->setDateSubmit(time());
        $log->setPart($part);
        $log->setDes($des);
        $log->setUser($user);
        $log->setBid($bid);
        $this->em->persist($log);
        $this->em->flush();
    }
}