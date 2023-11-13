<?php

namespace App\Controller;

use App\Entity\Shareholder;
use App\Service\Access;
use App\Service\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShareHolderController extends AbstractController
{
    #[Route('/api/shareholders/list', name: 'app_shareholders_list')]
    public function app_shareholders_list(Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        if(!$access->hasRole('shareholder'))
            throw $this->createAccessDeniedException();
        $datas = $entityManager->getRepository(Shareholder::class)->findBy([
            'bid'=>$request->headers->get('activeBid')
        ]);
        $resp = [];
        foreach($datas as $data){
            $temp = [];
            $temp['person'] = ['id'=>$data->getPerson()->getId(),'nikename'=>$data->getPerson()->getNikename()];
            $temp['percent'] = $data->getPercent();
            $resp[] = $temp;
        }
        return $this->json($resp);
    }
}
