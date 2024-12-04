<?php

namespace App\Controller;

use App\Entity\ChangeReport;
use App\Entity\HesabdariDoc;
use App\Entity\Statment;
use App\Entity\User;
use App\Service\Access;
use App\Service\Jdate;
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
    public function general_stat(EntityManagerInterface $entityManager,Jdate $jdate): JsonResponse
    {
        
        //get last version number
        $version = '0.0.1';
        $lastUpdateDate = '1399';
        $lastUpdateDes = '';
        $last = $entityManager->getRepository(ChangeReport::class)->findOneBy([],['id'=>'DESC']);
        if($last){
            $version =  $last->getVersion();
            $lastUpdateDate = $jdate->jdate('Y/n/d',$last->getDateSubmit());
            $lastUpdateDes = $last->getBody();
        }

        return $this->json([
            'version'=>$version,
            'lastUpdateDate'=>$lastUpdateDate,
            'lastUpdateDes'=>$lastUpdateDes,
        ]);
    }

    #[Route('/api/general/statements', name: 'general_statement')]
    public function general_statement(EntityManagerInterface $entityManager,Jdate $jdate): JsonResponse
    {
        return $this->json($entityManager->getRepository(Statment::class)->findBy([],['id'=>'DESC']));
    }

    #[Route('/api/general/get/time', name: 'general_get_time')]
    public function general_get_time(Jdate $jdate): JsonResponse
    {
        return $this->json(['timeNow'=>$jdate->jdate('Y/n/d',time())]);
    }
    #[Route('/front/print/{id}', name: 'app_front_print')]
    public function app_front_print(Provider $provider,EntityManagerInterface $entityManager,pdfMGR $pdfMGR,String $id)
    {
        $print = $entityManager->getRepository(PrinterQueue::class)->findOneBy(['pid'=>$id]);
        if(!$print)
            throw $this->createNotFoundException();
        if($print->isPosprint()){
            $pdfMGR->streamTwig2PDFInvoiceType($print);
        }
        else{
          $pdfMGR->streamTwig2PDF($print);  
        }
        
        return new Response('');
    }
}
