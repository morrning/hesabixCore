<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Entity\HesabdariRow;
use App\Service\Access;
use App\Service\Log;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BankController extends AbstractController
{
    #[Route('/api/bank/list', name: 'app_bank_list')]
    public function app_bank_list(Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        if(!$access->hasRole('banks'))
            throw $this->createAccessDeniedException();
        $datas = $entityManager->getRepository(BankAccount::class)->findBy([
            'bid'=>$request->headers->get('activeBid')
        ]);
        foreach($datas as $data){
            $bs = 0;
            $bd = 0;
            $items = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'bank'=>$data
            ]);
            foreach ($items as $item){
                $bs += $item->getBs();
                $bd += $item->getBd();
            }
            $data->setBalance($bd - $bs);
        }
        return $this->json($datas);
    }

    #[Route('/api/bank/info/{code}', name: 'app_bank_info')]
    public function app_bank_info($code,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('banks');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $data = $entityManager->getRepository(BankAccount::class)->findOneBy([
            'bid'=>$acc['bid'],
            'code'=>$code
        ]);
        return $this->json($data);
    }

    #[Route('/api/bank/mod/{code}', name: 'app_bank_mod')]
    public function app_bank_mod(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,$code = 0): JsonResponse
    {
        $acc = $access->hasRole('banks');
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
            $data = $entityManager->getRepository(BankAccount::class)->findOneBy([
                'name'=>$params['name'],
                'bid' =>$acc['bid']
            ]);
            //check exist before
            if($data)
                return $this->json(['result'=>2]);
            $data = new BankAccount();
            $data->setCode($provider->getAccountingCode($request->headers->get('activeBid'),'bank'));
        }
        else{
            $data = $entityManager->getRepository(BankAccount::class)->findOneBy([
                'bid'=>$acc['bid'],
                'code'=>$code
            ]);
            if(!$data)
                throw $this->createNotFoundException();
        }
        $data->setBid($acc['bid']);
        $data->setname($params['name']);
        $data->setDes($params['des']);
        $data->setOwner($params['owner']);
        $data->setAccountNum($params['accountNum']);
        $data->setCardNum($params['cardNum']);
        $data->setShaba($params['shaba']);
        $data->setShobe($params['shobe']);
        $data->setPosNum($params['posNum']);
        $data->setMobileInternetBank($params['mobileInternetbank']);
        $entityManager->persist($data);
        $entityManager->flush();
        $log->insert('بانک','حساب بانکی با نام  ' . $params['name'] . ' افزوده/ویرایش شد.',$this->getUser(),$request->headers->get('activeBid'));
        return $this->json(['result' => 1]);
    }
}
