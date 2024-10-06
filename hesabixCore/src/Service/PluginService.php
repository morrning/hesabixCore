<?php

namespace App\Service;

use App\Entity\Business;
use App\Entity\HesabdariDoc;
use App\Entity\Plugin;
use App\Entity\PlugRepserviceOrder;
use App\Entity\User;
use App\Module\RemoteAddress;
use Doctrine\ORM\EntityManagerInterface;

class PluginService
{

    private $em;
    private $remoteAddress;
    function __construct(EntityManagerInterface  $entityManager)
    {
        $this->em = $entityManager;
    }

    public function isActive(string $plugin, Business | string | null $bid = null  ): bool
    {
        if(is_string($bid))
            $bid = $this->em->getRepository(Business::class)->find($bid);
        $ps = $this->em->getRepository(Plugin::class)->findBy([
            'bid' =>$bid,
            'name'=>$plugin
        ]);
        foreach($ps as $p){
            if($p->getDateExpire() > time()){
                return true;
            } 
        }
        return false;
    }
}