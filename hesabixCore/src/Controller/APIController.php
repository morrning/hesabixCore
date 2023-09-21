<?php

namespace App\Controller;

use App\Entity\APIToken;
use App\Service\Access;
use App\Service\Log;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class APIController extends AbstractController
{

    /**
     * function to generate random strings
     * @param 		int 	$length 	number of characters in the generated string
     * @return 		string	a new string is created with random characters of the desired length
     */
    private function RandomString($length = 32) {
        return substr(str_shuffle(str_repeat($x='23456789ABCDEFGHJKLMNPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }

    #[Route('/api/business/api/list', name: 'app_business_api_list')]
    public function app_business_api_list(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('owner');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $items = $entityManager->getRepository(APIToken::class)->findBy([
            'bid'=>$acc['bid'],
        ]);
        return $this->json($provider->ArrayEntity2Array($items,1,['bid','submitter']));
    }

    #[Route('/api/business/api/new', name: 'app_business_api_new')]
    public function app_business_api_new(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('owner');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $api = new APIToken();
        $api->setBid($acc['bid']);
        $api->setSubmitter($acc['user']);
        $api->setDateExpire(0);
        $api->setToken($this->RandomString(32));
        $entityManager->persist($api);
        $entityManager->flush();
        return $this->json($provider->Entity2Array($api,1,['bid','submitter']));
    }

    #[Route('/api/business/api/remove/{token}', name: 'app_business_api_remove')]
    public function app_business_api_remove(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager , $token): JsonResponse
    {
        $acc = $access->hasRole('owner');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $api = $entityManager->getRepository(APIToken::class)->findOneBy([
            'token'=>$token,
            'bid'=>$acc['bid']
        ]);
        if(!$api) throw $this->createNotFoundException('API Token not found');
        $entityManager->remove($api);
        $entityManager->flush();
        return $this->json(['result'=>1]);
    }
}
