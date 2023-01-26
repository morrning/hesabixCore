<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class GeneralController extends AbstractController
{
    #[Route('/', name: 'general_home')]
    public function general_home(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to hesabix API.',
        ]);
    }
    #[Route('api/acc/dd', name: 'acc_dd')]
    public function acc_dd(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to hesabix API.',
        ]);
    }
}
