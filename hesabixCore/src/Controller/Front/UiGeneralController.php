<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\registryMGR;

class UiGeneralController extends AbstractController
{
    #[Route('/', name: 'general_home')]
    public function general_home(): JsonResponse
    {
        return $this->json(['message' => 'System is running ...']);
    }

    #[Route('/system/getname', name: 'general_get_name')]
    public function general_get_name(registryMGR $registryManager): JsonResponse
    {
        $name = $registryManager->get('system', 'appName');
        return $this->json($name);
    }

    #[Route('/system/geturl', name: 'general_get_url')]
    public function general_get_url(registryMGR $registryManager): JsonResponse
    {
        $url = $registryManager->get('system', 'appUrl');
        return $this->json($url);
    }
}
