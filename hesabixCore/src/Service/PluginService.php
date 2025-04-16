<?php

namespace App\Service;

use App\Entity\Business;
use App\Entity\Plugin;
use App\Entity\PluginProdect;
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
        // بررسی فعال بودن پلاگین به صورت پیش‌فرض
        $pluginProduct = $this->em->getRepository(PluginProdect::class)->findOneBy(['code' => $plugin]);
        if ($pluginProduct && $pluginProduct->isDefaultOn()) {
            return true;
        }

        // اگر پلاگین به صورت پیش‌فرض فعال نباشد، بررسی می‌کنیم که آیا برای این کسب‌وکار فعال است یا خیر
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