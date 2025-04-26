<?php

namespace App\Controller\Componenets;

use App\Entity\BankAccount;
use App\Service\Access;
use App\Service\Explore;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BankController extends AbstractController
{

    #[Route('/api/componenets/bank/get/{id}', name: 'app_componenets_bank_get')]
    public function app_componenets_bank_get(Access $access,EntityManagerInterface $entityManager, $id): JsonResponse
    {
        $bank = $entityManager->getRepository(BankAccount::class)->find($id);
        $acc = $access->hasRole('join');
        if (!$acc) {
            return new JsonResponse(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }
        if (!$bank) {
            return new JsonResponse(['message' => 'Bank not found'], Response::HTTP_NOT_FOUND);
        }
        if($bank->getBid() != $acc['bid']){
            return new JsonResponse(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }
        return new JsonResponse(Explore::ExploreBank($bank));
    }
}
