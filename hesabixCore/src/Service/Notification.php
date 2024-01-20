<?php

namespace App\Service;

use App\Entity\Business;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class Notification
{
    private $em;

    function __construct(EntityManagerInterface  $entityManager)
    {
        $this->em = $entityManager;
    }
    public function insert(string $message,string $url,Business | null $business,User $user): bool
    {
        $item = new \App\Entity\Notification();
        $item->setBid($business);
        $item->setDateSubmit(time());
        $item->setViewed(false);
        $item->setUser($user);
        $item->setMessage($message);
        $item->setUrl($url);
        $this->em->persist($item);
        $this->em->flush();
        return true;
    }
}