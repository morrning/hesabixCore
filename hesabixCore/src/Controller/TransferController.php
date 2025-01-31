<?php

namespace App\Controller;

use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Service\Access;
use App\Service\Log;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TransferController extends AbstractController
{
    #[Route('/api/transfer/search', name: 'app_transfer_search')]
    public function app_transfer_search(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('bankTransfer');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $items = $entityManager->getRepository(HesabdariDoc::class)->findBy([
            'bid'=>$acc['bid'],
            'type'=>'transfer',
            'year'=>$acc['year'],
            'money'=> $acc['money']
        ],
        ['dateSubmit'=>'DESC']);
        $resp = [];
        foreach ($items as $item){
            $temp = [];
            $temp['submitter']= $item->getSubmitter()->getFullName();
            $temp['code']= $item->getCode();
            $temp['date']= $item->getDate();
            $temp['des']= $item->getDes();
            $temp['amount']= $item->getAmount();
            $rows = $entityManager->getRepository(HesabdariRow::class)->findBy([
               'doc'=>$item
            ]);
            $fromType = '';
            $fromObject = '';
            $toType = '';
            $toObject = '';
            foreach ($rows as $row){
                if($row->getBs()!=0){
                    //it is from
                    if($row->getBank()){
                        $fromType = 'bank';
                        $fromObject = $row->getBank()->getName();
                    }
                    elseif($row->getSalary()){
                        $fromType = 'salary';
                        $fromObject = $row->getSalary()->getName();
                    }
                    elseif($row->getCashdesk()){
                        $fromType = 'cashDesk';
                        $fromObject = $row->getCashdesk()->getName();
                    }
                }
                else{
                    if($row->getBank()){
                        $toType = 'bank';
                        $toObject = $row->getBank()->getName();
                    }
                    elseif($row->getSalary()){
                        $toType = 'salary';
                        $toObject = $row->getSalary()->getName();
                    }
                    elseif($row->getCashdesk()){
                        $toType = 'cashDesk';
                        $toObject = $row->getCashdesk()->getName();
                    }
                }
            }
            $temp['fromType']= $fromType;
            $temp['fromObject']= $fromObject;
            $temp['toType']= $toType;
            $temp['toObject']= $toObject;
            $resp[] = $temp;
        }
        return $this->json($resp);
    }
}
