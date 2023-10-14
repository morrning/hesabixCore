<?php

namespace App\Controller;

use App\Entity\Business;
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
            $temps[] = $temp;
        }
        return $this->json(array_reverse($temps));
    }
}
