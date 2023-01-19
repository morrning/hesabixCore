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
        phpinfo();
        return $this->json([
            'message' => 'Welcome to hesabix API.',
        ]);
    }
}
