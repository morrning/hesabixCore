<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RfsellController extends AbstractController
{
    #[Route('/rfsell', name: 'app_rfsell')]
    public function index(): Response
    {
        return $this->render('rfsell/index.html.twig', [
            'controller_name' => 'RfsellController',
        ]);
    }
}
