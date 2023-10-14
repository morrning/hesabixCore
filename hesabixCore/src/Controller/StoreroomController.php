<?php

namespace App\Controller;

use App\Entity\Storeroom;
use App\Service\Access;
use App\Service\Log;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StoreroomController extends AbstractController
{
    #[Route('/api/storeroom/list', name: 'app_storeroom_list')]
    public function app_storeroom_list(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('store');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $items = $entityManager->getRepository(Storeroom::class)->findBy([
            'bid'=>$acc['bid']
        ]);

        return $this->json($provider->ArrayEntity2Array($items,0));
    }

    #[Route('/api/storeroom/mod/{code}', name: 'app_storeroom_mod')]
    public function app_storeroom_mod(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,$code = 0): JsonResponse
    {
        $acc = $access->hasRole('store');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(!array_key_exists('name',$params))
            return $this->json(['result'=>-1]);
        if(count_chars(trim($params['name'])) == 0)
            return $this->json(['result'=>3]);
        if($code == 0){
            $data = $entityManager->getRepository(Storeroom::class)->findOneBy([
                'name'=>$params['name'],
                'bid'=>$acc['bid']
            ]);
            //check exist before
            if($data)
                return $this->json(['result'=>2]);
            $data = new Storeroom();
            $data->setBid($acc['bid']);
        }
        else{
            $data = $entityManager->getRepository(Storeroom::class)->findOneBy([
                'bid'=>$acc['bid'],
                'id'=>$code
            ]);
            if(!$data)
                throw $this->createNotFoundException();
        }
        $data->setName($params['name']);
        $data->setAdr($params['adr']);
        $data->setManager($params['manager']);
        $data->setTel($params['tel']);
        if($params['active'] == 'true')
            $data->setActive(true);
        else
            $data->setActive(false);
        $entityManager->persist($data);
        $entityManager->flush();
        $log->insert('انبارداری','انبار ' . $params['name'] . ' افزوده/ویرایش شد.',$this->getUser(),$acc['bid']);
        return $this->json(['result' => 1]);
    }

    /**
     * @throws \ReflectionException
     */
    #[Route('/api/storeroom/info/{code}', name: 'app_storeroom_info')]
    public function app_storeroom_info($code,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('store');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $data = $entityManager->getRepository(Storeroom::class)->findOneBy([
            'bid'=>$acc['bid'],
            'id'=>$code
        ]);
        return $this->json($provider->Entity2Array($data,0));
    }

}
