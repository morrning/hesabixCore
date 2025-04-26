<?php

namespace App\Controller\Componenets;

use App\Entity\Cashdesk;
use App\Service\Access;
use App\Service\Explore;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CashdeskController extends AbstractController
{

    #[Route('/api/componenets/cashdesk/get/{id}', name: 'app_componenets_cashdesk_get')]
    public function app_componenets_cashdesk_get(Access $access,EntityManagerInterface $entityManager, $id): JsonResponse
    {
        $cashdesk = $entityManager->getRepository(Cashdesk::class)->find($id);
        $acc = $access->hasRole('join');
        if (!$acc) {
            return new JsonResponse(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }
        if (!$cashdesk) {
            return new JsonResponse(['message' => 'Cashdesk not found'], Response::HTTP_NOT_FOUND);
        }
        if($cashdesk->getBid() != $acc['bid']){
            return new JsonResponse(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }
        return new JsonResponse(Explore::ExploreCashdesk($cashdesk));
    }
}
