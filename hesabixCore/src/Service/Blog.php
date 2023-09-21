<?php

namespace App\Service;

use App\Entity\BlogCat;
use App\Entity\BlogComment;
use Doctrine\ORM\EntityManagerInterface;

class Blog
{
    private $em;
    function __construct(
        EntityManagerInterface  $entityManager,
    )
    {
        $this->em = $entityManager;
    }

    public function getCats() : Array{
       return $this->em->getRepository(BlogCat::class)->findAll();
    }

    public function getLastComments(){
        return $this->em->getRepository(BlogComment::class)->findLastComments();
    }
}