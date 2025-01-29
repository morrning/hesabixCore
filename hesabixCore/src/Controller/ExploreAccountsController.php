<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExploreAccountsController extends AbstractController
{
    #[Route('/explore/accounts', name: 'app_explore_accounts')]
    public function index(): JsonResponse
    {
        return $this->json([]);
    }
}
