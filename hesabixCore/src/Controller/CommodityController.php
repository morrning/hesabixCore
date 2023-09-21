<?php

namespace App\Controller;

use App\Entity\Commodity;
use App\Entity\CommodityUnit;
use App\Service\Access;
use App\Service\Log;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommodityController extends AbstractController
{
    #[Route('/api/commodity/list', name: 'app_commodity_list')]
    public function app_commodity_list(Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        if(!$access->hasRole('commodity'))
            throw $this->createAccessDeniedException();
        $items = $entityManager->getRepository(Commodity::class)->findBy([
            'bid'=>$request->headers->get('activeBid')
        ]);
        foreach ($items as $item){
            $item->setUnit($item->getUnit()->getName());
        }
        return $this->json($items);
    }

    #[Route('/api/commodity/info/{code}', name: 'app_commodity_info')]
    public function app_commodity_info($code,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $data = $entityManager->getRepository(Commodity::class)->findOneBy([
            'bid'=>$acc['bid'],
            'code'=>$code
        ]);
        $data->setUnit($data->getUnit()->getName());
        return $this->json($data);
    }

    #[Route('/api/commodity/mod/{code}', name: 'app_commodity_mod')]
    public function app_commodity_mod(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,$code = 0): JsonResponse
    {
        $acc = $access->hasRole('commodity');
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
            $data = $entityManager->getRepository(Commodity::class)->findOneBy([
                'name'=>$params['name']
            ]);
            //check exist before
            if($data)
                return $this->json(['result'=>2]);
            $data = new Commodity();
            $data->setCode($provider->getAccountingCode($request->headers->get('activeBid'),'Commodity'));
        }
        else{
            $data = $entityManager->getRepository(Commodity::class)->findOneBy([
                'bid'=>$acc['bid'],
                'code'=>$code
            ]);
            if(!$data)
                throw $this->createNotFoundException();
        }
        $unit = $entityManager->getRepository(CommodityUnit::class)->findOneBy(['name'=>$params['unit']]);
        if(!$unit)
            throw $this->createNotFoundException('unit not fount!');
        $data->setUnit($unit);
        $data->setBid($acc['bid']);
        $data->setname($params['name']);
        $data->setDes($params['des']);
        $data->setPriceSell($params['priceSell']);
        $data->setPriceBuy($params['priceBuy']);
        $entityManager->persist($data);
        $entityManager->flush();
        $log->insert('کالا و خدمات','کالا / خدمات با نام  ' . $params['name'] . ' افزوده/ویرایش شد.',$this->getUser(),$request->headers->get('activeBid'));
        return $this->json(['result' => 1]);
    }

    #[Route('/api/commodity/units', name: 'app_commodity_units')]
    public function app_commodity_units(Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        if(!$access->hasRole('commodity'))
            throw $this->createAccessDeniedException();
        $items = $entityManager->getRepository(CommodityUnit::class)->findAll();
        return $this->json($items);
    }
}

