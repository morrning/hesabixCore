<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MoadiyanController extends AbstractController
{
    #[Route('api/moadiyan', name: 'app_moadiyan')]
    public function index(): Response
    {
        return $this->render('moadiyan/index.html.twig', [
            'controller_name' => 'MoadiyanController',
        ]);
    }
}
