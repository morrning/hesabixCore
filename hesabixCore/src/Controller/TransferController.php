<?php

namespace App\Controller;

use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Entity\BankAccount;
use App\Entity\Salary;
use App\Entity\Cashdesk;
use App\Entity\HesabdariTable;
use App\Service\Access;
use App\Service\Log;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TransferController extends AbstractController
{
    #[Route('/api/transfer/search', name: 'app_transfer_search')]
    public function app_transfer_search(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('bankTransfer');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $items = $entityManager->getRepository(HesabdariDoc::class)->findBy(
            [
                'bid' => $acc['bid'],
                'type' => 'transfer',
                'year' => $acc['year'],
                'money' => $acc['money']
            ],
            ['dateSubmit' => 'DESC']
        );
        $resp = [];
        foreach ($items as $item) {
            $temp = [];
            $temp['submitter'] = $item->getSubmitter()->getFullName();
            $temp['code'] = $item->getCode();
            $temp['date'] = $item->getDate();
            $temp['des'] = $item->getDes();
            $temp['amount'] = $item->getAmount();
            $rows = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'doc' => $item
            ]);
            $fromType = '';
            $fromObject = '';
            $toType = '';
            $toObject = '';
            foreach ($rows as $row) {
                if ($row->getBs() != 0) {
                    //it is from
                    if ($row->getBank()) {
                        $fromType = 'bank';
                        $fromObject = $row->getBank()->getName();
                    } elseif ($row->getSalary()) {
                        $fromType = 'salary';
                        $fromObject = $row->getSalary()->getName();
                    } elseif ($row->getCashdesk()) {
                        $fromType = 'cashDesk';
                        $fromObject = $row->getCashdesk()->getName();
                    }
                } else {
                    if ($row->getBank()) {
                        $toType = 'bank';
                        $toObject = $row->getBank()->getName();
                    } elseif ($row->getSalary()) {
                        $toType = 'salary';
                        $toObject = $row->getSalary()->getName();
                    } elseif ($row->getCashdesk()) {
                        $toType = 'cashDesk';
                        $toObject = $row->getCashdesk()->getName();
                    }
                }
            }
            $temp['fromType'] = $fromType;
            $temp['fromObject'] = $fromObject;
            $temp['toType'] = $toType;
            $temp['toObject'] = $toObject;
            $resp[] = $temp;
        }
        return $this->json($resp);
    }

    #[Route('/api/transfer/insert', name: 'app_transfer_insert')]
    public function app_transfer_insert(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('bankTransfer');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        if (!array_key_exists('date', $params) || !array_key_exists('des', $params))
            throw $this->createNotFoundException('some params mistake');

        if (array_key_exists('update', $params) && $params['update'] != '') {
            $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                'bid' => $acc['bid'],
                'year' => $acc['year'],
                'code' => $params['update'],
                'money' => $acc['money']
            ]);
            if (!$doc)
                throw $this->createNotFoundException('document not found.');
            $doc->setDes($params['des']);
            $doc->setDate($params['date']);
            $doc->setMoney($acc['money']);

            $entityManager->persist($doc);
            $entityManager->flush();
            $rows = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'doc' => $doc
            ]);
            foreach ($rows as $row)
                $entityManager->remove($row);
        } else {
            $doc = new HesabdariDoc();
            $doc->setBid($acc['bid']);
            $doc->setYear($acc['year']);
            $doc->setDes($params['des']);
            $doc->setDateSubmit(time());
            $doc->setType('transfer');
            $doc->setDate($params['date']);
            $doc->setSubmitter($this->getUser());
            $doc->setMoney($acc['money']);
            $doc->setCode($provider->getAccountingCode($acc['bid'], 'accounting'));
            $entityManager->persist($doc);
            $entityManager->flush();
        }

        $amount = 0;
        foreach ($params['rows'] as $row) {
            $row['bs'] = str_replace(',', '', $row['bs']);
            $row['bd'] = str_replace(',', '', $row['bd']);

            $hesabdariRow = new HesabdariRow();
            $hesabdariRow->setBid($acc['bid']);
            $hesabdariRow->setYear($acc['year']);
            $hesabdariRow->setDoc($doc);
            $hesabdariRow->setBs($row['bs']);
            $hesabdariRow->setBd($row['bd']);
            $hesabdariRow->setDes($row['des']);
            $hesabdariRow->setReferral($row['referral']);
            if ($row['type'] == 'bank') {
                $bank = $entityManager->getRepository(BankAccount::class)->findOneBy([
                    'id' => $row['id'],
                    'bid' => $acc['bid']
                ]);
                if (!$bank)
                    throw $this->createNotFoundException('bank not found');
                $hesabdariRow->setBank($bank);
                $hesabdariRow->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code' => '5']));

            } elseif ($row['type'] == 'salary') {
                $salary = $entityManager->getRepository(Salary::class)->findOneBy([
                    'id' => $row['id'],
                    'bid' => $acc['bid']
                ]);
                if (!$salary)
                    throw $this->createNotFoundException('salary not found');
                $hesabdariRow->setSalary($salary);
                $hesabdariRow->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code' => '122']));

            } elseif ($row['type'] == 'cashdesk') {
                $cashdesk = $entityManager->getRepository(Cashdesk::class)->findOneBy([
                    'id' => $row['id'],
                    'bid' => $acc['bid']
                ]);
                if (!$cashdesk)
                    throw $this->createNotFoundException('cashdesk not found');
                $hesabdariRow->setCashdesk($cashdesk);
                $hesabdariRow->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code' => '121']));

            }

            $entityManager->persist($hesabdariRow);
            $amount += $row['bs'];
        }

        $doc->setAmount($amount);
        $entityManager->persist($doc);
        $entityManager->flush();

        $log->insert(
            'حسابداری',
            'سند انتقال وجه شماره ' . $doc->getCode() . ' ثبت / ویرایش شد.',
            $this->getUser(),
            $request->headers->get('activeBid'),
            $doc
        );

        return $this->json([
            'result' => 1,
            'doc' => $provider->Entity2Array($doc, 0)
        ]);
    }
}
