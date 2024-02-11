<?php

namespace App\Controller;

use App\Service\Log;
use App\Service\Access;
use App\Service\Explore;
use App\Entity\HesabdariDoc;
use App\Entity\StoreroomTicket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SellController extends AbstractController
{
    #[Route('/api/sell/edit/can/{code}', name: 'app_buy_can_edit')]
    public function app_buy_can_edit(Request $request,Access $access,Log $log,EntityManagerInterface $entityManager, string $code): JsonResponse
    {
        $canEdit = true;
        $acc = $access->hasRole('sell');
        if(!$acc)
            throw $this->createAccessDeniedException();

        $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
            'bid'=>$acc['bid'],
            'code'=>$code
        ]);
        //check related documents
        if(count($doc->getRelatedDocs()) != 0)
            $canEdit = false;

        //check storeroom tickets
        $tickets = $entityManager->getRepository(StoreroomTicket::class)->findBy(['doc'=>$doc]);
        if(count($tickets) != 0)
            $canEdit = false;
        return $this->json([
            'result'=> $canEdit
        ]);
    }

    #[Route('/api/sell/get/info/{code}', name: 'app_buy_get_info')]
    public function app_buy_get_info(Request $request,Access $access,Log $log,EntityManagerInterface $entityManager, string $code): JsonResponse
    {
        $acc = $access->hasRole('sell');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
            'bid'=>$acc['bid'],
            'code'=>$code
        ]);
        if(!$doc)
            throw $this->createNotFoundException();
        
        return $this->json(Explore::ExploreSellDoc($doc));
    }
}
