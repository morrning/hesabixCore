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
    #[Route('/api/shareholder/list', name: 'app_shareholder_list')]
    public function app_shareholder_list(Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        if(!$access->hasRole('shareholder'))
            throw $this->createAccessDeniedException();
        $datas = $entityManager->getRepository(Shareholder::class)->findBy([
            'bid'=>$request->headers->get('activeBid')
        ]);
        foreach($datas as $data){

        }
        return $this->json($datas);
    }
}
