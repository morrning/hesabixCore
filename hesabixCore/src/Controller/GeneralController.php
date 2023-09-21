<?php

namespace App\Controller;

use App\Entity\HesabdariDoc;
use App\Entity\User;
use App\Service\Access;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Business;
use App\Entity\PrinterQueue;
use App\Service\pdfMGR;
use App\Service\Provider;

class GeneralController extends AbstractController
{
    #[Route('/api/general/stat', name: 'general_stat')]
    public function general_stat(EntityManagerInterface $entityManager): JsonResponse
    {
        $busCount = count($entityManager->getRepository(Business::class)->findAll());
        $users = count($entityManager->getRepository(User::class)->findAll());
        $docs = count($entityManager->getRepository(HesabdariDoc::class)->findAll());
        $lastBusiness = $entityManager->getRepository(Business::class)->findLast();
        return $this->json([
            'business' => $busCount,
            'users'=> $users,
            'docs'=> $docs,
            'lastBusinessName'=> $lastBusiness->getname(),
            'lastBusinessOwner'=>$lastBusiness->getOwner()->getFullName()
        ]);
    }

    #[Route('/front/print/{id}', name: 'app_front_print')]
    public function app_front_print(Provider $provider,EntityManagerInterface $entityManager,pdfMGR $pdfMGR,String $id)
    {
        $print = $entityManager->getRepository(PrinterQueue::class)->findOneBy(['pid'=>$id]);
        if(!$print)
            throw $this->createNotFoundException();
        $pdfMGR->streamTwig2PDF($print);
        return new Response('');
    }
}
