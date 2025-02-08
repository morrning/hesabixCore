<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Entity\HesabdariRow;
use App\Service\Access;
use App\Service\Explore;
use App\Service\Log;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
class BankController extends AbstractController
{
    #[Route('/api/bank/list', name: 'app_bank_list')]
    public function app_bank_list(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('banks');
        if (!$acc)
            throw $this->createAccessDeniedException();

        //bug fix for bank with no money type
        $datas = $entityManager->getRepository(BankAccount::class)->findBy([
            'bid' => $request->headers->get('activeBid'),
            'money' => null
        ]);
        foreach ($datas as $data) {
            $data->setMoney($acc['bid']->getMoney());
            $entityManager->persist($data);
        }
        $entityManager->flush();
        //end of bug fix

        $datas = $entityManager->getRepository(BankAccount::class)->findBy([
            'bid' => $request->headers->get('activeBid'),
            'money' => $acc['money']
        ]);
        foreach ($datas as $data) {
            $bs = 0;
            $bd = 0;
            $items = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'bank' => $data
            ]);
            foreach ($items as $item) {
                $bs += $item->getBs();
                $bd += $item->getBd();
            }
            $data->setBalance($bd - $bs);
        }
        return $this->json($provider->ArrayEntity2Array($datas, 0));
    }

    #[Route('/api/bank/info/{code}', name: 'app_bank_info')]
    public function app_bank_info($code, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('banks');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $data = $entityManager->getRepository(BankAccount::class)->findOneBy([
            'bid' => $acc['bid'],
            'money' => $acc['money'],
            'code' => $code
        ]);
        return $this->json(data: Explore::ExploreBank($data));
    }

    #[Route('/api/bank/mod/{code}', name: 'app_bank_mod')]
    public function app_bank_mod(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('banks');
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
            $data = $entityManager->getRepository(BankAccount::class)->findOneBy([
                'name' => $params['name'],
                'bid' => $acc['bid']
            ]);
            //check exist before
            if ($data)
                return $this->json(['result' => 2]);
            $data = new BankAccount();
            $data->setCode($provider->getAccountingCode($request->headers->get('activeBid'), 'bank'));
            $data->setMoney($acc['money']);
        } else {
            $data = $entityManager->getRepository(BankAccount::class)->findOneBy([
                'bid' => $acc['bid'],
                'code' => $code
            ]);
            if (!$data)
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
        $log->insert('بانک', 'حساب بانکی با نام  ' . $params['name'] . ' افزوده/ویرایش شد.', $this->getUser(), $request->headers->get('activeBid'));
        return $this->json(['result' => 1]);
    }

    #[Route('/api/bank/delete/{code}', name: 'app_bank_delete')]
    public function app_bank_delete(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('banks');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $bank = $entityManager->getRepository(BankAccount::class)->findOneBy(['bid' => $acc['bid'], 'code' => $code]);
        if (!$bank)
            throw $this->createNotFoundException();
        //check accounting docs
        $rows = $entityManager->getRepository(HesabdariRow::class)->findby(['bid' => $acc['bid'], 'bank' => $bank]);
        if (count($rows) > 0)
            return $this->json(['result' => 2]);
        if ($acc['bid']->getWalletMatchBank()) {
            if ($acc['bid']->getWalletMatchBank()->getId() == $bank->getId())
                return $this->json(['result' => 3]);
        }

        $name = $bank->getName();
        $entityManager->remove($bank);
        $log->insert('بانکداری', ' حساب بانکی  با نام ' . $name . ' حذف شد. ', $this->getUser(), $acc['bid']->getId());
        return $this->json(['result' => 1]);
    }

    /**
     * @throws Exception
     */
    #[Route('/api/bank/card/list/excel', name: 'app_bank_card_list_excel')]
    public function app_bank_card_list_excel(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): BinaryFileResponse|JsonResponse|StreamedResponse
    {
        $acc = $access->hasRole('banks');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('code', $params))
            throw $this->createNotFoundException();
        $bank = $entityManager->getRepository(BankAccount::class)->findOneBy(['bid' => $acc['bid'], 'code' => $params['code']]);
        if (!$bank)
            throw $this->createNotFoundException();
        if (!array_key_exists('items', $params)) {
            $transactions = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'bid' => $acc['bid'],
                'bank' => $bank,
                'year'=>$acc['year']
            ]);
        } else {
            $transactions = [];
            foreach ($params['items'] as $param) {
                $prs = $entityManager->getRepository(HesabdariRow::class)->findOneBy([
                    'id' => $param['id'],
                    'bid' => $acc['bid'],
                    'bank' => $bank,
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
                $transaction->getRef()->getName(),
                $transaction->getBs(),
                $transaction->getBd(),
                $transaction->getYear()->getlabel()
            ];
        }
        $activeWorksheet->fromArray($arrayEntity, null, 'A1');
        $activeWorksheet->setRightToLeft(true);
        $writer = new Xlsx($spreadsheet);
        $filePath = __DIR__ . '/../../var/' . $provider->RandomString(12) . '.xlsx';
        $writer->save($filePath);
        return new BinaryFileResponse($filePath);
    }

    #[Route('/api/bank/card/list/print', name: 'app_bank_card_list_print')]
    public function app_bank_card_list_print(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('banks');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('code', $params))
            throw $this->createNotFoundException();
        $bank = $entityManager->getRepository(BankAccount::class)->findOneBy(['bid' => $acc['bid'], 'code' => $params['code']]);
        if (!$bank)
            throw $this->createNotFoundException();

        if (!array_key_exists('items', $params)) {
            $transactions = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'bid' => $acc['bid'],
                'bank' => $bank,
                'year'=>$acc['year']
            ]);
        } else {
            $transactions = [];
            foreach ($params['items'] as $param) {
                $prs = $entityManager->getRepository(HesabdariRow::class)->findOneBy([
                    'id' => $param['id'],
                    'bid' => $acc['bid'],
                    'bank' => $bank,
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
            $this->renderView('pdf/bank_card.html.twig', [
                'page_title' => 'کارت حساب' . ' ' . $bank->getName(),
                'bid' => $acc['bid'],
                'items' => $transactions,
                'bank' => $bank
            ])
        );
        return $this->json(['id' => $pid]);
    }
}
