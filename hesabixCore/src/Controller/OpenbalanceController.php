<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Entity\Cashdesk;
use App\Entity\HesabdariDoc;
use App\Entity\HesabdariTable;
use App\Entity\Person;
use App\Entity\Salary;
use App\Entity\Shareholder;
use App\Service\Access;
use App\Service\Explore;
use App\Service\Extractor;
use App\Service\Jdate;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Proxies\__CG__\App\Entity\HesabdariRow;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OpenbalanceController extends AbstractController
{
    #[Route('/api/openbalance/get', name: 'app_openbalance_get')]
    public function app_openbalance_get(Access $access, EntityManagerInterface $entityManagerInterface, Extractor $extractor): Response
    {
        $acc = $access->hasRole('accounting');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $res = [];

        //get open balance doc
        $doc = $entityManagerInterface->getRepository(HesabdariDoc::class)->findOneBy([
            'year' => $acc['year'],
            'bid' => $acc['bid'],
            'type' => 'open_balance',
            'money' => $acc['money']
        ]);
        if (!$doc)
            $doc = new HesabdariDoc();

        //get banks
        $banks = $entityManagerInterface->getRepository(BankAccount::class)->findBy([
            'bid' => $acc['bid'],
            'money' => $acc['money']
        ]);
        $banksDet = [];
        foreach ($banks as $bank) {
            $temp = [];
            $temp['info'] = Explore::ExploreBank($bank);
            foreach ($doc->getHesabdariRows() as $row) {
                if ($row->getBank() == $bank) {
                    $temp['openbalance'] = $row->getBd();
                }
            }
            $banksDet[] = $temp;
        }
        $res['banks'] = $banksDet;

        //get cashdesks
        $cashdesks = $entityManagerInterface->getRepository(Cashdesk::class)->findBy([
            'bid' => $acc['bid'],
            'money' => $acc['money']
        ]);
        $cashdesksDet = [];
        foreach ($cashdesks as $cashdesk) {
            $temp = [];
            $temp['info'] = Explore::ExploreCashdesk($cashdesk);
            foreach ($doc->getHesabdariRows() as $row) {
                if ($row->getCashdesk() == $cashdesk) {
                    $temp['openbalance'] = $row->getBd();
                }
            }
            $cashdesksDet[] = $temp;
        }
        $res['cashdesks'] = $cashdesksDet;

        //get salarys
        $salarys = $entityManagerInterface->getRepository(Salary::class)->findBy([
            'bid' => $acc['bid'],
            'money' => $acc['money']
        ]);
        $salaryDet = [];
        foreach ($salarys as $salary) {
            $temp = [];
            $temp['info'] = Explore::ExploreSalary($salary);
            foreach ($doc->getHesabdariRows() as $row) {
                if ($row->getSalary() == $salary) {
                    $temp['openbalance'] = $row->getBd();
                }
            }
            $salaryDet[] = $temp;
        }
        $res['salarys'] = $salaryDet;

        //get shareholders
        $shareholders = $entityManagerInterface->getRepository(Shareholder::class)->findBy([
            'bid' => $acc['bid'],
        ]);
        $shareholderDet = [];
        foreach ($shareholders as $shareholder) {
            $temp = [];
            $temp['info'] = Explore::ExplorePerson($shareholder->getPerson());
            foreach ($doc->getHesabdariRows() as $row) {
                if ($row->getPerson() == $shareholder->getPerson() && $row->getRefData() == 'shareholder') {
                    $temp['openbalance'] = $row->getBs();
                }
            }
            $shareholderDet[] = $temp;
        }
        $res['shareholders'] = $shareholderDet;

        return $this->json($extractor->operationSuccess($res));
    }

    #[Route('/api/openbalance/save/banks', name: 'app_openbalance_save_banks')]
    public function app_openbalance_save_banks(Provider $provider,Jdate $jdate, Request $request, Access $access, EntityManagerInterface $entityManagerInterface, Extractor $extractor): Response
    {
        $acc = $access->hasRole('accounting');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        //get open balance doc
        $doc = $entityManagerInterface->getRepository(HesabdariDoc::class)->findOneBy([
            'year' => $acc['year'],
            'bid' => $acc['bid'],
            'type' => 'open_balance',
            'money' => $acc['money']
        ]);
        if (!$doc) {
            $doc = new HesabdariDoc();
            $doc->setBid($acc['bid']);
            $doc->setAmount(0);
            $doc->setDateSubmit(time());
            $doc->setMoney($acc['money']);
            $doc->setSubmitter($this->getUser());
            $doc->setYear($acc['year']);
            $doc->setDes('سند افتتاحیه');
            $doc->setDate($jdate->jdate('Y/n/d', time()));
            $doc->setType('open_balance');
            $doc->setCode($provider->getAccountingCode($acc['bid'],'accounting'));
            $entityManagerInterface->persist($doc);
        }

        foreach ($params as $param) {
            $bank = $entityManagerInterface->getRepository(BankAccount::class)->findOneBy([
                'code' => $param['info']['code'],
                'bid' => $acc['bid'],
                'money' => $acc['money']
            ]);
            $ExistBefore = false;
            foreach ($doc->getHesabdariRows() as $row) {
                if ($row->getBank() == $bank) {
                    if ($param['openbalance'] != 0) {
                        $ExistBefore = true;
                        $row->setBd($param['openbalance']);
                        $entityManagerInterface->persist($row);
                    } else {
                        $doc->removeHesabdariRow($row);
                    }
                }
            }
            if ((!$ExistBefore) && $param['openbalance'] != 0) {
                $row = new HesabdariRow();
                $row->setDoc($doc);
                $row->setBank($bank);
                $row->setBd($param['openbalance']);
                $row->setBs(0);
                $row->setBid($acc['bid']);
                $row->setYear($acc['year']);
                $row->setDes('موجودی اول دوره');
                $row->setRef($entityManagerInterface->getRepository(HesabdariTable::class)->findOneBy(['code' => 5]));
                $entityManagerInterface->persist($row);
            }
        }

        //calculate amount of document
        foreach ($doc->getHesabdariRows() as $row) {
            $doc->setAmount($doc->getAmount() + $row->getBd());
        }
        $entityManagerInterface->persist($doc);

        $entityManagerInterface->flush();
        return $this->json($extractor->operationSuccess());
    }

    #[Route('/api/openbalance/save/cashdesks', name: 'app_openbalance_save_cashdesk')]
    public function app_openbalance_save_cashdesk(Provider $provider,Jdate $jdate, Request $request, Access $access, EntityManagerInterface $entityManagerInterface, Extractor $extractor): Response
    {
        $acc = $access->hasRole('accounting');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        //get open balance doc
        $doc = $entityManagerInterface->getRepository(HesabdariDoc::class)->findOneBy([
            'year' => $acc['year'],
            'bid' => $acc['bid'],
            'type' => 'open_balance',
            'money' => $acc['money']
        ]);
        if (!$doc) {
            $doc = new HesabdariDoc();
            $doc->setBid($acc['bid']);
            $doc->setAmount(0);
            $doc->setDateSubmit(time());
            $doc->setMoney($acc['money']);
            $doc->setSubmitter($this->getUser());
            $doc->setYear($acc['year']);
            $doc->setDes('سند افتتاحیه');
            $doc->setDate($jdate->jdate('Y/n/d', time()));
            $doc->setType('open_balance');
            $doc->setCode($provider->getAccountingCode($acc['bid'],'accounting'));
            $entityManagerInterface->persist($doc);
        }

        foreach ($params as $param) {
            $cashdesk = $entityManagerInterface->getRepository(Cashdesk::class)->findOneBy([
                'code' => $param['info']['code'],
                'bid' => $acc['bid'],
                'money' => $acc['money']
            ]);
            $ExistBefore = false;
            foreach ($doc->getHesabdariRows() as $row) {
                if ($row->getCashdesk() == $cashdesk) {
                    if ($param['openbalance'] != 0) {
                        $ExistBefore = true;
                        $row->setBd($param['openbalance']);
                        $entityManagerInterface->persist($row);
                    } else {
                        $doc->removeHesabdariRow($row);
                    }
                }
            }
            if ((!$ExistBefore) && $param['openbalance'] != 0) {
                $row = new HesabdariRow();
                $row->setDoc($doc);
                $row->setCashdesk($cashdesk);
                $row->setBd($param['openbalance']);
                $row->setBs(0);
                $row->setBid($acc['bid']);
                $row->setYear($acc['year']);
                $row->setDes('موجودی اول دوره');
                $row->setRef($entityManagerInterface->getRepository(HesabdariTable::class)->findOneBy(['code' => 5]));
                $entityManagerInterface->persist($row);
            }
        }

        //calculate amount of document
        foreach ($doc->getHesabdariRows() as $row) {
            $doc->setAmount($doc->getAmount() + $row->getBd());
        }
        $entityManagerInterface->persist($doc);

        $entityManagerInterface->flush();
        return $this->json($extractor->operationSuccess());
    }

    #[Route('/api/openbalance/save/salarys', name: 'app_openbalance_save_salary')]
    public function app_openbalance_save_salary(Provider $provider,Jdate $jdate, Request $request, Access $access, EntityManagerInterface $entityManagerInterface, Extractor $extractor): Response
    {
        $acc = $access->hasRole('accounting');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        //get open balance doc
        $doc = $entityManagerInterface->getRepository(HesabdariDoc::class)->findOneBy([
            'year' => $acc['year'],
            'bid' => $acc['bid'],
            'type' => 'open_balance',
            'money' => $acc['money']
        ]);
        if (!$doc) {
            $doc = new HesabdariDoc();
            $doc->setBid($acc['bid']);
            $doc->setAmount(0);
            $doc->setDateSubmit(time());
            $doc->setMoney($acc['money']);
            $doc->setSubmitter($this->getUser());
            $doc->setYear($acc['year']);
            $doc->setDes('سند افتتاحیه');
            $doc->setDate($jdate->jdate('Y/n/d', time()));
            $doc->setType('open_balance');
            $doc->setCode($provider->getAccountingCode($acc['bid'],'accounting'));
            $entityManagerInterface->persist($doc);
        }

        foreach ($params as $param) {
            $salary = $entityManagerInterface->getRepository(Salary::class)->findOneBy([
                'code' => $param['info']['code'],
                'bid' => $acc['bid'],
                'money' => $acc['money']
            ]);
            $ExistBefore = false;
            foreach ($doc->getHesabdariRows() as $row) {
                if ($row->getSalary() == $salary) {
                    if ($param['openbalance'] != 0) {
                        $ExistBefore = true;
                        $row->setBd($param['openbalance']);
                        $entityManagerInterface->persist($row);
                    } else {
                        $doc->removeHesabdariRow($row);
                    }
                }
            }
            if ((!$ExistBefore) && $param['openbalance'] != 0) {
                $row = new HesabdariRow();
                $row->setDoc($doc);
                $row->setSalary($salary);
                $row->setBd($param['openbalance']);
                $row->setBs(0);
                $row->setBid($acc['bid']);
                $row->setYear($acc['year']);
                $row->setDes('موجودی اول دوره');
                $row->setRef($entityManagerInterface->getRepository(HesabdariTable::class)->findOneBy(['code' => 5]));
                $entityManagerInterface->persist($row);
            }
        }

        //calculate amount of document
        foreach ($doc->getHesabdariRows() as $row) {
            $doc->setAmount($doc->getAmount() + $row->getBd());
        }
        $entityManagerInterface->persist($doc);

        $entityManagerInterface->flush();
        return $this->json($extractor->operationSuccess());
    }

    #[Route('/api/openbalance/save/shareholders', name: 'app_openbalance_save_shareholder')]
    public function app_openbalance_save_shareholder(Provider $provider,Jdate $jdate, Request $request, Access $access, EntityManagerInterface $entityManagerInterface, Extractor $extractor): Response
    {
        $acc = $access->hasRole('accounting');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        //get open balance doc
        $doc = $entityManagerInterface->getRepository(HesabdariDoc::class)->findOneBy([
            'year' => $acc['year'],
            'bid' => $acc['bid'],
            'type' => 'open_balance',
            'money' => $acc['money']
        ]);
        if (!$doc) {
            $doc = new HesabdariDoc();
            $doc->setBid($acc['bid']);
            $doc->setAmount(0);
            $doc->setDateSubmit(time());
            $doc->setMoney($acc['money']);
            $doc->setSubmitter($this->getUser());
            $doc->setYear($acc['year']);
            $doc->setDes('سند افتتاحیه');
            $doc->setDate($jdate->jdate('Y/n/d', time()));
            $doc->setType('open_balance');
            $doc->setCode($provider->getAccountingCode($acc['bid'],'accounting'));
            $entityManagerInterface->persist($doc);
        }

        foreach ($params as $param) {
            $person = $entityManagerInterface->getRepository(Person::class)->findOneBy([
                'code' => $param['info']['code'],
                'bid' => $acc['bid'],
            ]);
            if(!$person) return $this->json($extractor->operationFail());

            $shareholder = $entityManagerInterface->getRepository(Shareholder::class)->findOneBy([
                'person' => $person,
                'bid' => $acc['bid'],
            ]);
            $ExistBefore = false;
            foreach ($doc->getHesabdariRows() as $row) {
                if ($row->getPerson() == $shareholder->getPerson() && $row->getRefData() == 'shareholder') {
                    if ($param['openbalance'] != 0) {
                        $ExistBefore = true;
                        $row->setBs($param['openbalance']);
                        $entityManagerInterface->persist($row);
                    } else {
                        $doc->removeHesabdariRow($row);
                    }
                }
            }
            if ((!$ExistBefore) && $param['openbalance'] != 0) {
                $row = new HesabdariRow();
                $row->setDoc($doc);
                $row->setPerson($shareholder->getPerson());
                $row->setBs($param['openbalance']);
                $row->setBd(0);
                $row->setRefData('shareholder');
                $row->setBid($acc['bid']);
                $row->setYear($acc['year']);
                $row->setDes('موجودی اول دوره');
                $row->setRef($entityManagerInterface->getRepository(HesabdariTable::class)->findOneBy(['code' => 5]));
                $entityManagerInterface->persist($row);
            }
        }

        //calculate amount of document
        foreach ($doc->getHesabdariRows() as $row) {
            $doc->setAmount($doc->getAmount() + $row->getBd());
        }
        $entityManagerInterface->persist($doc);

        $entityManagerInterface->flush();
        return $this->json($extractor->operationSuccess());
    }
}
