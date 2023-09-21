<?php

namespace App\Controller;

use App\Service\pdfMGR;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AdsBanController extends AbstractController
{
    #[Route('/ads/ban', name: 'app_ads_ban')]
    public function index(pdfMGR $pdfMGR): JsonResponse
    {
        $pdfMGR->streamTwig2PDF('test.html.twig');
    }
}
