<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\Year;
use App\Service\Jdate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class YearController extends AbstractController
{
    #[Route('/api/year/list', name: 'app_year_list')]
    public function app_year_list(Request $request,EntityManagerInterface $entityManager): JsonResponse
    {
        $business = $entityManager->getRepository(Business::class)->find($request->headers->get('activeBid'));
        if(!$business)
            throw $this->createNotFoundException();
        $years = $entityManager->getRepository(Year::class)->findBy([
           'bid'=>$business
        ]);
        return $this->json($years);
    }

    #[Route('/api/year/get', name: 'app_year_get')]
    public function app_year_get(Jdate $jdate,Request $request,EntityManagerInterface $entityManager): JsonResponse
    {
        $business = $entityManager->getRepository(Business::class)->find($request->headers->get('activeBid'));
        if(!$business)
            throw $this->createNotFoundException();
        $year = $entityManager->getRepository(Year::class)->find($request->headers->get('activeYear'));
        if(!$year)
            throw $this->createNotFoundException();
        $yearLoad = $entityManager->getRepository(Year::class)->findOneBy([
            'id'=> $year->getId(),
            'bid'=>$business
        ]);
        $yearLoad->setStart($jdate->jdate('Y/m/d',$yearLoad->getStart()));
        $yearLoad->setEnd($jdate->jdate('Y/m/d',$yearLoad->getEnd()));
        $yearLoad->setNow($jdate->jdate('Y/m/d',time()));
        return $this->json($yearLoad);
    }

}
