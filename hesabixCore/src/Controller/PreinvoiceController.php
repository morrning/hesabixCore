<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PreinvoiceController extends AbstractController
{
    #[Route('/preinvoice', name: 'app_preinvoice')]
    public function index(): Response
    {
        return $this->render('preinvoice/index.html.twig', [
            'controller_name' => 'PreinvoiceController',
        ]);
    }
}
