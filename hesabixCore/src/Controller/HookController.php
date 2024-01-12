<?php

namespace App\Controller;

use App\Entity\APIToken;
use App\Entity\BankAccount;
use App\Entity\Commodity;
use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Entity\Money;
use App\Entity\Permission;
use App\Entity\Person;
use App\Entity\Plugin;
use App\Entity\User;
use App\Entity\Business;
use App\Entity\Hook;
use App\Entity\Year;
use App\Service\Access;
use App\Service\Jdate;
use App\Service\Log;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;

use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;


class HookController extends AbstractController
{
    #[Route('hooks/setting/SetChangeHook', name: 'api_hook_SetChangeHook')]
    public function api_hook_SetChangeHook(Access $access,Log $log,Request $request,EntityManagerInterface $entityManager): JsonResponse
    { 
        $api = $entityManager->getRepository(APIToken::class)->findOneBy([
            'token' => $request->headers->get('api-key'),
        ]);
       
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        $hook = $entityManager->getRepository(Hook::class)->findOneBy([
            'url'=> $params['url'],
            'password'=> $params['hookPassword'],
            'bid' => $api->getBid(),
            'submitter'=>$this->getUser()
        ]);
        if(!$hook){
            $hook = new Hook();
            $hook->setBid($api->getBid());
            $hook->setSubmitter($this->getUser());
            $hook->setPassword($params['hookPassword']);
            $hook->setUrl($params['url']);
            $entityManager->persist($hook);
            $entityManager->flush();
        }
        
        $year = $entityManager->getRepository(Year::class)->findOneBy(['bid'=>$api->getBid(),'head'=>true])->getId();
        return $this->json([
            'Success'=>true,
            'bid' => $api->getBid()->getId(),
            'year' => $year,
            'money' => $api->getBid()->getMoney()->getId()
        ]);
    }

    #[Route('hooks/setting/getCurrency', name: 'api_hooks_getcurrency')]
    public function api_hooks_getcurrency(Access $access,Log $log,Request $request,EntityManagerInterface $entityManager): JsonResponse
    {
        $api = $entityManager->getRepository(APIToken::class)->findOneBy([
            'token' => $request->headers->get('api-key'),
        ]);
        if(!$api)
            throw $this->createNotFoundException();

            return $this->json([
                'Success'=>true,
                'ErrorCode' => 0,
                'ErrorMessage' => '',
                'Result' =>[
                    'moneyId' =>  $api->getBid()->getMoney()->getId(),
                    'moneyName' => $api->getBid()->getMoney()->getName(),
                    'moneylabel' => $api->getBid()->getMoney()->getLabel()
                ]
            ]);
    }
}
