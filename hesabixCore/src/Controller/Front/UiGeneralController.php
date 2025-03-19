<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UiGeneralController extends AbstractController
{
    #[Route('/', name: 'general_home')]
    public function general_home(): JsonResponse
    {
        return $this->json(['message'=>'System is running ...']);
    }
}
