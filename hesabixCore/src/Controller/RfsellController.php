<?php

namespace App\Controller;

use App\Service\Log;
use App\Service\Access;
use App\Entity\HesabdariDoc;
use App\Entity\StoreroomTicket;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Explore as ServiceExplore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class RfsellController extends AbstractController
{
    #[Route('/api/rfsell/edit/can/{code}', name: 'app_rfsell_can_edit')]
    public function app_rfsell_can_edit(Request $request,Access $access,Log $log,EntityManagerInterface $entityManager, string $code): JsonResponse
    {
        $canEdit = true;
        $acc = $access->hasRole('plugAccproRfsell');
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

    #[Route('/api/rfsell/get/info/{code}', name: 'app_rfsell_get_info')]
    public function app_rfsell_get_info(Request $request,Access $access,Log $log,EntityManagerInterface $entityManager, string $code): JsonResponse
    {
        $acc = $access->hasRole('plugAccproRfsell');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
            'bid'=>$acc['bid'],
            'code'=>$code
        ]);
        if(!$doc)
            throw $this->createNotFoundException();
        
        return $this->json(ServiceExplore::ExploreRfsellDoc($doc));
    }
}
