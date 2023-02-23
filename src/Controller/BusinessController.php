<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Business;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class BusinessController extends AbstractController
{
    #[Route('/api/business/list', name: 'api_bussiness_list')]
    public function api_login(#[CurrentUser] ?User $user,EntityManagerInterface $entityManager): Response
    {
        $buss = $entityManager->getRepository(Business::class)->findBy(['owner'=>$user]);
        $response = [];
        foreach ($buss as $bus){
            $temp = [];
            $temp['id'] = $bus->getId();
            $temp['name'] = $bus->getName();
            $temp['owner'] = $bus->getOwner()->getFullName();
            $temp['legal_name'] = $bus->getLegalName();
            $response[] = $temp;
        }

        return $this->json($response);
    }

    #[Route('/api/business/list/count', name: 'api_bussiness_list_count')]
    public function api_bussiness_list_count(#[CurrentUser] ?User $user,EntityManagerInterface $entityManager): Response
    {
        $buss = $entityManager->getRepository(Business::class)->findBy(['owner'=>$user]);
        $response = ['count'=>count($buss)];
        return $this->json($response);
    }
}
