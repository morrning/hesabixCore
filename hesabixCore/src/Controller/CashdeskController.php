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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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

    #[Route('/api/cashdesk/search', name: 'app_cashdesk_search')]
    public function app_cashdesk_search(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('cashdesk');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        $query = $entityManager->createQueryBuilder()
            ->select('c')
            ->from(Cashdesk::class, 'c')
            ->where('c.bid = :bid')
            ->andWhere('c.money = :money')
            ->setParameter('bid', $acc['bid'])
            ->setParameter('money', $acc['money']);

        if (isset($params['search']) && !empty($params['search'])) {
            $query->andWhere('c.name LIKE :search')
                ->setParameter('search', '%' . $params['search'] . '%');
        }

        if (isset($params['page']) && isset($params['itemsPerPage'])) {
            $query->setFirstResult(($params['page'] - 1) * $params['itemsPerPage'])
                ->setMaxResults($params['itemsPerPage']);
        }

        $datas = $query->getQuery()->getResult();
        
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
        }

        return $this->json([
            'items' => $provider->ArrayEntity2Array($datas, 0),
            'total' => count($datas)
        ]);
    }

    #[Route('/api/cashdesk/balance/{code}', name: 'app_cashdesk_balance')]
    public function app_cashdesk_balance($code, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('cashdesk');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $cashdesk = $entityManager->getRepository(Cashdesk::class)->findOneBy([
            'bid' => $acc['bid'],
            'money' => $acc['money'],
            'code' => $code
        ]);

        if (!$cashdesk)
            throw $this->createNotFoundException();

        $bs = 0;
        $bd = 0;
        $items = $entityManager->getRepository(HesabdariRow::class)->findBy([
            'cashdesk' => $cashdesk
        ]);

        foreach ($items as $item) {
            $bs += $item->getBs();
            $bd += $item->getBd();
        }

        return $this->json([
            'balance' => $bd - $bs,
            'debit' => $bd,
            'credit' => $bs
        ]);
    }

    #[Route('/api/cashdesk/transactions/{code}', name: 'app_cashdesk_transactions')]
    public function app_cashdesk_transactions($code, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('cashdesk');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        $cashdesk = $entityManager->getRepository(Cashdesk::class)->findOneBy([
            'bid' => $acc['bid'],
            'money' => $acc['money'],
            'code' => $code
        ]);

        if (!$cashdesk)
            throw $this->createNotFoundException();

        $query = $entityManager->createQueryBuilder()
            ->select('r')
            ->from(HesabdariRow::class, 'r')
            ->where('r.cashdesk = :cashdesk')
            ->andWhere('r.bid = :bid')
            ->setParameter('cashdesk', $cashdesk)
            ->setParameter('bid', $acc['bid']);

        if (isset($params['startDate']) && isset($params['endDate'])) {
            $query->andWhere('r.doc.date BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $params['startDate'])
                ->setParameter('endDate', $params['endDate']);
        }

        if (isset($params['page']) && isset($params['itemsPerPage'])) {
            $query->setFirstResult(($params['page'] - 1) * $params['itemsPerPage'])
                ->setMaxResults($params['itemsPerPage']);
        }

        $transactions = $query->getQuery()->getResult();

        return $this->json([
            'items' => $provider->ArrayEntity2Array($transactions, 0),
            'total' => count($transactions)
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/api/cashdesk/card/list/excel', name: 'app_cashdesk_card_list_excel')]
    public function app_cashdesk_card_list_excel(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): BinaryFileResponse|JsonResponse|StreamedResponse
    {
        $acc = $access->hasRole('cashdesk');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('code', $params))
            throw $this->createNotFoundException();
        $cashdesk = $entityManager->getRepository(Cashdesk::class)->findOneBy(['bid' => $acc['bid'], 'code' => $params['code']]);
        if (!$cashdesk)
            throw $this->createNotFoundException();
        if (!array_key_exists('items', $params)) {
            $transactions = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'bid' => $acc['bid'],
                'cashdesk' => $cashdesk,
                'year'=>$acc['year']
            ]);
        } else {
            $transactions = [];
            foreach ($params['items'] as $param) {
                $prs = $entityManager->getRepository(HesabdariRow::class)->findOneBy([
                    'id' => $param['id'],
                    'bid' => $acc['bid'],
                    'cashdesk' => $cashdesk,
                    'year' => $acc['year']
                ]);
                if ($prs) {
                    $transactions[] = $prs;
                }
            }
        }
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $arrayEntity = [
            [
                'شماره تراکنش',
                'تاریخ',
                'توضیحات',
                'شرح سند',
                'تفضیل',
                'بستانکار',
                'بدهکار',
                'سال مالی',
            ]
        ];
        foreach ($transactions as $transaction) {
            $arrayEntity[] = [
                $transaction->getId(),
                $transaction->getDoc()->getDate(),
                $transaction->getDes(),
                $transaction->getDoc()->getDes(),
                $transaction->getRef()->getName(),
                $transaction->getBs(),
                $transaction->getBd(),
                $transaction->getYear()->getlabel()
            ];
        }
        $activeWorksheet->fromArray($arrayEntity, null, 'A1');
        $activeWorksheet->setRightToLeft(true);
        $writer = new Xlsx($spreadsheet);
        $fileName = 'کارت حساب صندوق ' . $cashdesk->getName() . '.xlsx';
        $filePath = __DIR__ . '/../../var/' . $fileName;
        $writer->save($filePath);
        
        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        );
        return $response;
    }

    #[Route('/api/cashdesk/card/list/print', name: 'app_cashdesk_card_list_print')]
    public function app_cashdesk_card_list_print(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('cashdesk');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('code', $params))
            throw $this->createNotFoundException();
        $cashdesk = $entityManager->getRepository(Cashdesk::class)->findOneBy(['bid' => $acc['bid'], 'code' => $params['code']]);
        if (!$cashdesk)
            throw $this->createNotFoundException();

        if (!array_key_exists('items', $params)) {
            $transactions = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'bid' => $acc['bid'],
                'cashdesk' => $cashdesk,
                'year'=>$acc['year']
            ]);
        } else {
            $transactions = [];
            foreach ($params['items'] as $param) {
                $prs = $entityManager->getRepository(HesabdariRow::class)->findOneBy([
                    'id' => $param['id'],
                    'bid' => $acc['bid'],
                    'cashdesk' => $cashdesk,
                    'year'=>$acc['year']
                ]);
                if ($prs) {
                    $transactions[] = $prs;
                }
            }
        }
        $pid = $provider->createPrint(
            $acc['bid'],
            $this->getUser(),
            $this->renderView('pdf/cashdesk_card.html.twig', [
                'page_title' => 'کارت حساب' . ' ' . $cashdesk->getName(),
                'bid' => $acc['bid'],
                'items' => $transactions,
                'cashdesk' => $cashdesk
            ])
        );
        return $this->json(['id' => $pid]);
    }
}
