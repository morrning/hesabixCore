<?php

namespace App\Service;

use App\Entity\Registry;
use Doctrine\ORM\EntityManagerInterface;

class registryMGR
{
    private $em;

    function __construct(EntityManagerInterface  $entityManager)
    {
        $this->em = $entityManager;
    }
    public function update(string $root,string $key ,string $v): bool
    {
       $item = $this->em->getRepository(Registry::class)->findOneBy([
        'root'=>$root,
        'name'=>$key
       ]);
       if(! $item){
        $item = new Registry();
       }
       $item->setRoot($root);
       $item->setName($key);
       $item->setValueOfKey($v);
       $this->em->persist($item);
       $this->em->flush();
       return true;
    }

    public function get(string $root,string $key){
        $item = $this->em->getRepository(Registry::class)->findOneBy([
            'root'=>$root,
            'name'=>$key
           ]);
           if(! $item){
            $item = new Registry();
            $item->setRoot($root);
            $item->setName($key);
            $item->setValueOfKey('');
            $this->em->persist($item);
            $this->em->flush();
           }
           return $item->getValueOfKey();
    }
}