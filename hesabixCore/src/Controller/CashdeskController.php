<?php

namespace App\Controller;

use App\Entity\Cashdesk;
use App\Entity\HesabdariRow;
use App\Service\Access;
use App\Service\Explore;
use App\Service\Log;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CashdeskController extends AbstractController
{
    #[Route('/api/cashdesk/list', name: 'app_cashdesk_list')]
    public function app_cashdesk_list(Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('cashdesk');
        if (!$acc)
            throw $this->createAccessDeniedException();
        //bug fix for bank with no money type
        $datas = $entityManager->getRepository(Cashdesk::class)->findBy([
            'bid' => $request->headers->get('activeBid'),
            'money' => null
        ]);
        foreach ($datas as $data) {
            $data->setMoney($acc['bid']->getMoney());
            $entityManager->persist($data);
        }
        $entityManager->flush();
        //end of bug fix

        $datas = $entityManager->getRepository(Cashdesk::class)->findBy([
            'bid' => $request->headers->get('activeBid'),
            'money' => $acc['money']
        ]);
        $resp = [];
        foreach ($datas as $data) {
            $bs = 0;
            $bd = 0;
            $items = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'cashdesk' => $data
            ]);
            foreach ($items as $item) {
                $bs += $item->getBs();
                $bd += $item->getBd();
            }
            $data->setBalance($bd - $bs);
            $resp[] = Explore::ExploreCashdesk($data);
        }
        return $this->json($resp);
    }

    #[Route('/api/cashdesk/info/{code}', name: 'app_cashdesk_info')]
    public function app_cashdesk_info($code, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('cashdesk');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $data = $entityManager->getRepository(Cashdesk::class)->findOneBy([
            'bid' => $acc['bid'],
            'code' => $code,
            'money' => $acc['money']
        ]);
        return $this->json(Explore::ExploreCashdesk($data));
    }

    #[Route('/api/cashdesk/mod/{code}', name: 'app_cashdesk_mod')]
    public function app_cashdesk_mod(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('cashdesk');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('name', $params))
            return $this->json(['result' => -1]);
        if (count_chars(trim($params['name'])) == 0)
            return $this->json(['result' => 3]);
        if ($code == 0) {
            $data = $entityManager->getRepository(Cashdesk::class)->findOneBy([
                'name' => $params['name'],
                'bid' => $acc['bid']
            ]);
            //check exist before
            if ($data)
                return $this->json(['result' => 2]);
            $data = new Cashdesk();
            $data->setCode($provider->getAccountingCode($request->headers->get('activeBid'), 'cashdesk'));
            $data->setMoney($acc['money']);
        } else {
            $data = $entityManager->getRepository(Cashdesk::class)->findOneBy([
                'bid' => $acc['bid'],
                'code' => $code
            ]);
            if (!$data)
                throw $this->createNotFoundException();
            if (!$data->getMoney()) {
                $data->setMoney($acc['money']);
            }
        }
        $data->setBid($acc['bid']);
        $data->setname($params['name']);
        $data->setDes($params['des']);
        $entityManager->persist($data);
        $entityManager->flush();
        $log->insert('بانک‌داری', ' صندوق با نام  ' . $params['name'] . ' افزوده/ویرایش شد.', $this->getUser(), $request->headers->get('activeBid'));
        return $this->json(['result' => 1]);
    }

    #[Route('/api/cashdesk/delete/{code}', name: 'app_cashdesk_delete')]
    public function app_cashdesk_delete(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('cashdesk');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $cashdesk = $entityManager->getRepository(Cashdesk::class)->findOneBy(['bid' => $acc['bid'], 'code' => $code]);
        if (!$cashdesk)
            throw $this->createNotFoundException();
        //check accounting docs
        $rows = $entityManager->getRepository(HesabdariRow::class)->findby(['bid' => $acc['bid'], 'cashdesk' => $cashdesk]);
        if (count($rows) > 0)
            return $this->json(['result' => 2]);

        $name = $cashdesk->getName();
        $entityManager->remove($cashdesk);
        $log->insert('بانکداری', '  صندوق  با نام ' . $name . ' حذف شد. ', $this->getUser(), $acc['bid']->getId());
        return $this->json(['result' => 1]);
    }
}
