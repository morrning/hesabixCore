<?php

namespace App\Service;

use App\Entity\APIToken;
use App\Entity\Business;
use App\Entity\Permission;
use App\Entity\Printer;
use App\Entity\PrintItem;
use App\Entity\UserToken;
use App\Entity\Year;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class Printers
{
    private $em;
   
    function __construct(EntityManagerInterface  $entityManager,){
        $this->em = $entityManager;
    }

    public function addFile(string $filename,array $acc,string $type){
        $printers = $this->em->getRepository(Printer::class)->findBy([
            'bid'=>$acc['bid']
        ]);
        foreach($printers as $printer){
            $item = new PrintItem();
            $item->setPrinted(false);
            $item->setPrinter($printer);
            $item->setType($type);
            $item->setFile($filename);
            $this->em->persist($item);
        }
        $this->em->flush();
    }
}