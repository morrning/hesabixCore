<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\HesabdariDoc;
use App\Entity\Log as EntityLog;
use App\Entity\PlugRepserviceOrder;
use App\Service\Access;
use App\Service\Jdate;
use App\Service\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LogController extends AbstractController
{
    #[Route('/api/business/logs/{bid}', name: 'api_business_logs')]
    public function api_business_logs(Request $request,Access $access,String $bid, Jdate $jdate, EntityManagerInterface $entityManager,Log $log): JsonResponse
    {
        if(!$access->hasRole('log'))
            throw $this->createAccessDeniedException();
        $business = $entityManager->getRepository(Business::class)->find($bid);
        if(!$business)
            throw $this->createNotFoundException();
        $params = [];
        $logs = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(array_key_exists('type',$params)){
            if($params['type'] == 'sms')
                $logs = $entityManager->getRepository(\App\Entity\Log::class)->findBy(['bid'=>$business,'part'=>'پیامک']);
            elseif($params['type'] == 'wallet')
                $logs = $entityManager->getRepository(\App\Entity\Log::class)->findBy(['bid'=>$business,'part'=>'کیف پول']);
        }
        else{
            $logs = $entityManager->getRepository(\App\Entity\Log::class)->findBy(['bid'=>$business]);
        }
        $temps = [];
        foreach ($logs as $log){
            $temp = [];
            if($log->getUser())
                $temp['user'] = $log->getUser()->getFullName();
            else
                $temp['user'] = '';
            $temp['des'] = $log->getDes();
            $temp['part'] = $log->getPart();
            $temp['date'] = $jdate->jdate('Y/n/d H:i',$log->getDateSubmit());
            $temp['ipaddress'] = $log->getIpaddress();
            $temps[] = $temp;
        }
        return $this->json(array_reverse($temps));
    }

    #[Route('/api/business/logs/doc/{id}', name: 'api_business_doc_logs')]
    public function api_business_doc_logs(Access $access,String $id, Jdate $jdate, EntityManagerInterface $entityManager,Log $log): JsonResponse
    {
        $acc = $access->hasRole('log');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy(['bid'=>$acc['bid'], 'code'=>$id]);
        if(!$doc)
            throw $this->createNotFoundException();
        if($doc->getBid()->getId() != $acc['bid']->getId())
            throw $this->createAccessDeniedException();
        
        $logs = $entityManager->getRepository(\App\Entity\Log::class)->findBy(['bid'=>$acc['bid'],'doc'=>$doc],['id'=>'DESC']);

        $temps = [];
        foreach ($logs as $log){
            $temp = [];
            if($log->getUser())
                $temp['user'] = $log->getUser()->getFullName();
            else
                $temp['user'] = '';
            $temp['des'] = $log->getDes();
            $temp['part'] = $log->getPart();
            $temp['date'] = $jdate->jdate('Y/n/d H:i',$log->getDateSubmit());
            $temps[] = $temp;
        }
        return $this->json($temps);
    }

    #[Route('/api/plug/repservice/order/logs/{id}', name: 'api_business_repservice_order_logs')]
    public function api_business_repservice_order_logs(Access $access,String $id, Jdate $jdate, EntityManagerInterface $entityManager,Log $log): JsonResponse
    {
        $acc = $access->hasRole('plugRepservice');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $order = $entityManager->getRepository(PlugRepserviceOrder::class)->findOneBy(['bid'=>$acc['bid'], 'code'=>$id]);
        if(!$order)
            throw $this->createNotFoundException();
        if($order->getBid()->getId() != $acc['bid']->getId())
            throw $this->createAccessDeniedException();
        
        $logs = $entityManager->getRepository(\App\Entity\Log::class)->findBy(['bid'=>$acc['bid'],'repserviceOrder'=>$order],['id'=>'DESC']);

        $temps = [];
        foreach ($logs as $log){
            $temp = [];
            if($log->getUser())
                $temp['user'] = $log->getUser()->getFullName();
            else
                $temp['user'] = '';
            $temp['des'] = $log->getDes();
            $temp['part'] = $log->getPart();
            $temp['date'] = $jdate->jdate('Y/n/d H:i',$log->getDateSubmit());
            $temps[] = $temp;
        }
        return $this->json($temps);
    }
}
