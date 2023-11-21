<?php

namespace App\Controller;

use App\Entity\WalletTransaction;
use App\Service\Access;
use App\Service\Jdate;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class WalletController extends AbstractController
{
    #[Route('/api/wallet/info', name: 'api_wallet_info')]
    public function api_wallet_info(EntityManagerInterface $entityManager,Access $access,Provider $provider): JsonResponse
    {
        $acc = $access->hasRole('wallet');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $items = $entityManager->getRepository(WalletTransaction::class)->findBy([
            'bid' => $acc['bid']
        ]);
        $pays = 0;
        $gets = 0;
        foreach ($items as $item){
            if($item->getType() == 'pay') $pays += $item->getAmount();
            elseif (($item->getType() == 'get' || $item->getType() == 'sell') && $item->getStatus() == 100 ) $gets += $item->getAmount();
        }
        return $this->json([
            'deposit' => $gets - $pays,
            'transactions'=>count($items),
            'turnover'=>$pays + $gets,
        ]);
    }
    #[Route('/api/wallet/transactions/{type}', name: 'api_wallet_transactions')]
    public function api_wallet_transactions(Jdate $jdate,EntityManagerInterface $entityManager,Access $access,Provider $provider, string $type = 'all'): JsonResponse
    {
        $acc = $access->hasRole('wallet');
        if(!$acc)
            throw $this->createAccessDeniedException();
        if($type == 'all'){
            $items = $entityManager->getRepository(WalletTransaction::class)->findBy([
                'bid' => $acc['bid']
            ],['id'=>'DESC']);
        }
        elseif($type == 'pay'){
            $items = $entityManager->getRepository(WalletTransaction::class)->findBy([
                'bid' => $acc['bid'],
                'type' => 'pay'
            ],['id'=>'DESC']);
        }
        elseif($type == 'income'){
            $items = $entityManager->getRepository(WalletTransaction::class)->findAllIncome($acc['bid']);
        }
        foreach ($items as $item){
            $item->setDateSubmit($jdate->jdate('Y/n/d H:i',$item->getDateSubmit()));
        }
        return $this->json($provider->ArrayEntity2Array($items,0));

    }
}
