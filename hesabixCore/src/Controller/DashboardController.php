<?php

namespace App\Controller;

use App\Entity\DashboardSettings;
use App\Service\Access;
use App\Service\Explore;
use App\Service\Extractor;
use App\Service\JsonResp;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/api/dashboard/settings/load', name: 'app_dashboard_load')]
    public function app_dashboard_load(Extractor $extractor, Access $access, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $setting = $entityManagerInterface->getRepository(DashboardSettings::class)->findOneBy([
            'submitter' => $this->getUser(),
            'bid' => $acc['bid']
        ]);
        if (!$setting) {
            $setting = new DashboardSettings();
            $setting->setSubmitter($this->getUser());
            $setting->setBid($acc['bid']);
            $entityManagerInterface->persist($setting);
            $entityManagerInterface->flush();
        }

        return $this->json($extractor->operationSuccess(Explore::ExploreDashboardSettings($setting)));
    }
    #[Route('/api/dashboard/settings/save', name: 'app_dashboard_save')]
    public function app_dashboard_save(Request $request,Extractor $extractor, Access $access, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $setting = $entityManagerInterface->getRepository(DashboardSettings::class)->findOneBy([
            'submitter' => $this->getUser(),
            'bid' => $acc['bid']
        ]);
        if (!$setting) {
            $setting = new DashboardSettings();
            $setting->setSubmitter($this->getUser());
            $setting->setBid($acc['bid']);
        }
        $params = $request->getPayload()->all();
        if(array_key_exists('banks',$params)) $setting->setBanks($params['banks']);
        if(array_key_exists('buys',$params)) $setting->setBuys($params['buys']);
        if(array_key_exists('sells',$params)) $setting->setSells($params['sells']);
        if(array_key_exists('wallet',$params)) $setting->setWallet($params['wallet']);
        if(array_key_exists('acc_docs',$params)) $setting->setAccDocs($params['acc_docs']);
        if(array_key_exists('accounting_total',$params)) $setting->setAccountingTotal($params['accounting_total']);
        if(array_key_exists('commodities',$params)) $setting->setCommodities($params['commodities']);
        if(array_key_exists('persons',$params)) $setting->setPersons($params['persons']);
        if(array_key_exists('notif',$params)) $setting->setNotif($params['notif']);

        $entityManagerInterface->persist($setting);
        $entityManagerInterface->flush();

        return $this->json($extractor->operationSuccess(Explore::ExploreDashboardSettings($setting)));
    }
}
