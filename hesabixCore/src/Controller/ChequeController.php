<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Entity\Cheque;
use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Entity\HesabdariTable;
use App\Entity\Person;
use App\Service\Log;
use App\Service\Jdate;
use App\Service\Access;
use App\Service\Explore;
use App\Service\JsonResp;
use App\Service\Provider;
use App\Service\SMS;
use App\Service\PluginService;
use App\Service\registryMGR;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChequeController extends AbstractController
{
    #[Route('/api/cheque/list', name: 'app_cheque_list')]
    public function app_accounting_insert(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, Jdate $jdate): JsonResponse
    {
        $acc = $access->hasRole('cheque');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $money = $acc['money'];
        $defaultMoey = $acc['bid']->getMoney();
        $defMoney = false;
        if ($defaultMoey->getId() == $money->getId()) {
            $defMoney = true;
        }
        $qb = $entityManager->createQueryBuilder();
        $chequesInput = $qb->select('c')
            ->from(Cheque::class, 'c')
            ->where('c.bid = :bid')
            ->andWhere('c.type = :type')
            ->andWhere($defMoney ? '(c.money = :money OR c.money IS NULL)' : 'c.money = :money')
            ->setParameter('bid', $acc['bid'])
            ->setParameter('type', 'input')
            ->setParameter('money', $money)
            ->getQuery()
            ->getResult();

        $qb = $entityManager->createQueryBuilder();
        $chequesOutput = $qb->select('c')
            ->from(Cheque::class, 'c')
            ->where('c.bid = :bid')
            ->andWhere('c.type = :type')
            ->andWhere($defMoney ? '(c.money = :money OR c.money IS NULL)' : 'c.money = :money')
            ->setParameter('bid', $acc['bid'])
            ->setParameter('type', 'output')
            ->setParameter('money', $money)
            ->getQuery()
            ->getResult();

        return $this->json([
            'input' => Explore::SerializeCheques(array_reverse($chequesInput)),
            'output' => Explore::SerializeCheques(array_reverse($chequesOutput))
        ]);
    }

    #[Route('/api/cheque/list/forpay', name: 'app_cheque_list_for_pay')]
    public function app_cheque_list_for_pay(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, Jdate $jdate): JsonResponse
    {
        $acc = $access->hasRole('cheque');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $cheques = $entityManager->getRepository(Cheque::class)->findBy([
            'bid' => $acc['bid'],
            'type' => 'input',
            'locked' => false,
        ]);

        return $this->json(
            Explore::SerializeCheques(array_reverse($cheques)),
        );
    }

    #[Route('/api/cheque/reject/{id}', name: 'app_cheque_reject')]
    public function app_cheque_reject(string $id, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, Jdate $jdate, SMS $SMS, registryMGR $registryMGR, PluginService $pluginService): JsonResponse
    {
        $acc = $access->hasRole('cheque');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $cheque = $entityManager->getRepository(Cheque::class)->findOneBy([
            'bid' => $acc['bid'],
            'id' => $id
        ]);
        if (!$cheque)
            throw $this->createNotFoundException('cheque not found');
        $cheque->setStatus('برگشت خورده');
        $cheque->setRejected(true);
        $cheque->setLocked(true);
        $entityManager->persist($cheque);

        $hesabdariDoc = new HesabdariDoc;
        $hesabdariDoc->setBid($acc['bid']);
        $hesabdariDoc->setSubmitter($this->getUser());
        $hesabdariDoc->setYear($acc['year']);
        $hesabdariDoc->setMoney($acc['money']);
        $hesabdariDoc->setDateSubmit(time());
        $hesabdariDoc->setType('reject_cheque');
        $hesabdariDoc->setCode($provider->getAccountingCode($acc['bid'], 'accounting'));
        $hesabdariDoc->setDate($cheque->getDate());
        $hesabdariDoc->setDes('برگشت خورده چک شماره ' . $cheque->getNumber());
        $hesabdariDoc->setAmount($cheque->getAmount());
        $entityManager->persist($hesabdariDoc);

        $hesabdariRow1 = new HesabdariRow;
        $hesabdariRow1->setDoc($hesabdariDoc);
        $hesabdariRow1->setCheque($cheque);
        $hesabdariRow1->setPerson($cheque->getPerson());
        $hesabdariRow1->setYear($acc['year']);
        $hesabdariRow1->setBs(0);
        $hesabdariRow1->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code' => 126]));
        $hesabdariRow1->setBd($cheque->getAmount());
        $hesabdariRow1->setBid($acc['bid']);
        $hesabdariRow1->setDes('برگشت خورده چک شماره ' . $cheque->getNumber());
        $entityManager->persist($hesabdariRow1);

        $hesabdariRow2 = new HesabdariRow;
        $hesabdariRow2->setDoc($hesabdariDoc);
        $hesabdariRow2->setCheque($cheque);
        $hesabdariRow2->setYear($acc['year']);
        $hesabdariRow2->setBs($cheque->getAmount());
        $hesabdariRow2->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code' => 125]));
        $hesabdariRow2->setBd(0);
        $hesabdariRow2->setBid($acc['bid']);
        $hesabdariRow2->setDes('برگشت خورده چک شماره ' . $cheque->getNumber());
        $entityManager->persist($hesabdariRow2);

        //send sms
        $params = $request->getPayload()->all();
        if (array_key_exists('sendSms', $params) && $params['sendSms'] == true && $registryMGR->get('sms', 'chequeReject') != ''  && $registryMGR->get('sms', 'plugAccproChequeReject') != '') {
            $person = $cheque->getPerson();
            if ($person && $person->getMobile() && !$pluginService->isActive('accpro', $acc['bid'])) {
                $SMS->sendByBalance(
                    [$person->getNikename(), $cheque->getNumber(), number_format($cheque->getAmount()), $cheque->getBankoncheque(), $cheque->getDate(), $acc['bid']->getName(), $acc['bid']->getName()],
                    $registryMGR->get('sms', 'chequeReject'),
                    $person->getMobile(),
                    $acc['bid'],
                    $this->getUser(),
                    3
                );
            } elseif ($person && $person->getMobile() && $pluginService->isActive('accpro', $acc['bid'])) {
                $SMS->sendByBalance(
                    [$person->getNikename(), $cheque->getNumber(), number_format($cheque->getAmount()), $cheque->getBankoncheque(), $cheque->getDate(), $acc['bid']->getName(), $acc['bid']->getName()],
                    $registryMGR->get('sms', 'plugAccproChequeReject'),
                    $person->getMobile(),
                    $acc['bid'],
                    $this->getUser(),
                    2
                );
            }
        }
        $entityManager->flush();
        $log->insert('بانکداری', 'چک شماره ' . $cheque->getNumber() . ' به برگشت خورده تغییر یافت. ', $this->getUser(), $request->headers->get('activeBid'), $hesabdariDoc);

        return $this->json(['result' => 'ok']);
    }

    #[Route('/api/cheque/pass/input/{id}', name: 'app_cheque_pass_input')]
    public function app_cheque_pass_input(string $id, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, Jdate $jdate): JsonResponse
    {
        $acc = $access->hasRole('cheque');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (! array_key_exists('bank', $params) || ! array_key_exists('date', $params))
            $this->createNotFoundException();
        $cheque = $entityManager->getRepository(Cheque::class)->findOneBy([
            'bid' => $acc['bid'],
            'type' => 'input',
            'id' => $id
        ]);
        $bank = $entityManager->getRepository(BankAccount::class)->findOneBy([
            'bid' => $acc['bid'],
            'id' => $params['bank']
        ]);
        if (!$cheque || !$bank)
            throw $this->createNotFoundException();
        if ($cheque->isLocked())
            throw $this->createAccessDeniedException('cheque operation not permitted');

        //edit cheque info
        $cheque->setBank($bank);
        $cheque->setStatus('وصول');
        $cheque->setPayDate($params['date']);
        $cheque->setLocked(true);
        $entityManager->persist($cheque);

        //create cheque document
        $hesabdariDoc = new HesabdariDoc;
        $hesabdariDoc->setBid($acc['bid']);
        $hesabdariDoc->setSubmitter($this->getUser());
        $hesabdariDoc->setYear($acc['year']);
        $hesabdariDoc->setMoney($acc['money']);
        $hesabdariDoc->setDateSubmit(time());
        $hesabdariDoc->setDate($params['date']);
        $hesabdariDoc->setType('pass_cheque');
        $hesabdariDoc->setCode($provider->getAccountingCode($acc['bid'], 'accounting'));
        $hesabdariDoc->setDes($params['des']);
        $hesabdariDoc->setAmount($cheque->getAmount());
        $entityManager->persist($hesabdariDoc);

        //cheate hesabdari rows
        $hesabdariRow1 = new HesabdariRow();
        $hesabdariRow1->setDoc($hesabdariDoc);
        $hesabdariRow1->setCheque($cheque);
        $hesabdariRow1->setYear($acc['year']);
        $hesabdariRow1->setBs($cheque->getAmount());
        $hesabdariRow1->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code' => 126]));
        $hesabdariRow1->setBd(0);
        $hesabdariRow1->setBid($acc['bid']);
        $hesabdariRow1->setDes('وصول چک و انتقال به بانک');
        $entityManager->persist($hesabdariRow1);

        $hesabdariRow2 = new HesabdariRow();
        $hesabdariRow2->setDoc($hesabdariDoc);
        $hesabdariRow2->setCheque($cheque);
        $hesabdariRow2->setBank($bank);
        $hesabdariRow2->setYear($acc['year']);
        $hesabdariRow2->setBs(0);
        $hesabdariRow2->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code' => 125]));
        $hesabdariRow2->setBd($cheque->getAmount());
        $hesabdariRow2->setBid($acc['bid']);
        $hesabdariRow2->setDes('وصول چک و انتقال به بانک');
        $entityManager->persist($hesabdariRow2);
        $entityManager->flush();
        $log->insert(
            'حسابداری',
            'ثبت چک وصول شماره ' . $cheque->getNumber() . ' و ثبت واریز به بانک ' . $bank->getName(),
            $this->getUser(),
            $acc['bid']->getId(),
            $hesabdariDoc
        );

        return $this->json([
            'result' => 'ok'
        ]);
    }

    #[Route('/api/cheque/modify/input/{id}', name: 'app_cheque_modify_input')]
    public function app_cheque_modify_input(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, Jdate $jdate, SMS $SMS, PluginService $pluginService, \App\Service\registryMGR $registryMGR, string $id = '0'): JsonResponse
    {
        $acc = $access->hasRole('cheque');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if ($id == '0') {
            $cheque = new Cheque;
            $cheque->setLocked(false);
            $cheque->setDateSubmit(time());
            $cheque->setDateStamp(time());
            $cheque->setSubmitter($this->getUser());
            $cheque->setBid($acc['bid']);
            $cheque->setMoney($acc['money']);
            $cheque->setStatus('وصول نشده');
            $cheque->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code' => 125]));
        } else {
            $cheque = $entityManager->getRepository(Cheque::class)->findOneBy(['id' => $id, 'bid' => $acc['bid']]);
            if (!$cheque)
                throw $this->createNotFoundException('cheque not found');
            if ($cheque->isRejected() || $cheque->getStatus() === 'وصول شده')
                throw $this->createAccessDeniedException('امکان ویرایش این چک وجود ندارد');
        }
        $cheque->setNumber($params['number']);
        if (!is_numeric($params['amount'])) {
            throw new \Exception('مبلغ باید عددی باشد');
        }
        $cheque->setAmount(strpos($params['amount'], '.') === false ? (int)$params['amount'] : (float)$params['amount']);
        $cheque->setType('input');
        $cheque->setBankoncheque($params['bankoncheque']);
        $cheque->setPerson($entityManager->getRepository(Person::class)->findOneBy(['id' => $params['person']['code'], 'bid' => $acc['bid']]));
        $cheque->setSayadNum($params['sayadNumber']);
        $cheque->setDate($params['date']);
        $cheque->setDes($params['description']);
        $entityManager->persist($cheque);

        // ارسال پیامک در صورت درخواست
        if (isset($params['sendSms']) && $params['sendSms'] == true) {
            $person = $cheque->getPerson();
            if ($person && $person->getMobile()) {
                if ($pluginService->isActive('accpro', $acc['bid']) && $person->getMobile() != '' && $acc['bid']->getTel()) {
                    $SMS->sendByBalance(
                        [$person->getNikename(), $cheque->getNumber(), number_format($cheque->getAmount()), $cheque->getBankoncheque(), $cheque->getDate(), $acc['bid']->getName(), $acc['bid']->getName()],
                        $registryMGR->get('sms', 'plugAccproChequeInput'),
                        $person->getMobile(),
                        $acc['bid'],
                        $this->getUser(),
                        3
                    );
                } else {
                    $SMS->sendByBalance(
                        [$person->getNikename(), $cheque->getNumber(), number_format($cheque->getAmount()), $cheque->getBankoncheque(), $cheque->getDate(), $acc['bid']->getName(), $acc['bid']->getName()],
                        $registryMGR->get('sms', 'chequeInput'),
                        $person->getMobile(),
                        $acc['bid'],
                        $this->getUser(),
                        2
                    );
                }
                $log->insert(
                    'بانکداری',
                    'ارسال پیامک به ' . $person->getNikename() . ' برای چک شماره ' . $cheque->getNumber(),
                    $this->getUser(),
                    $acc['bid']->getId()
                );
            }
        }

        // اگر چک موجود است، سند حسابداری قبلی را پیدا و به‌روزرسانی می‌کنیم
        if ($id != '0') {
            $hesabdariRow = $entityManager->getRepository(HesabdariRow::class)->findOneBy([
                'cheque' => $cheque,
                'bid' => $acc['bid']
            ]);
            $hesabdariDoc = $hesabdariRow ? $hesabdariRow->getDoc() : null;

            if ($hesabdariDoc) {
                // حذف سطرهای حسابداری قبلی
                $oldRows = $hesabdariDoc->getHesabdariRows();
                foreach ($oldRows as $row) {
                    $entityManager->remove($row);
                }
            } else {
                $hesabdariDoc = new HesabdariDoc;
            }
        } else {
            $hesabdariDoc = new HesabdariDoc;
        }

        $hesabdariDoc->setBid($acc['bid']);
        $hesabdariDoc->setSubmitter($this->getUser());
        $hesabdariDoc->setYear($acc['year']);
        $hesabdariDoc->setMoney($acc['money']);
        $hesabdariDoc->setDateSubmit(time());
        $hesabdariDoc->setType('modify_cheque');
        $hesabdariDoc->setCode($provider->getAccountingCode($acc['bid'], 'accounting'));
        $hesabdariDoc->setDate($params['date']);
        $hesabdariDoc->setDes($params['description']);
        $hesabdariDoc->setAmount($cheque->getAmount());
        $entityManager->persist($hesabdariDoc);
        $entityManager->persist($hesabdariDoc);



        // ایجاد سطرهای حسابداری جدید
        $hesabdariRow1 = new HesabdariRow;
        $hesabdariRow1->setDoc($hesabdariDoc);
        $hesabdariRow1->setCheque($cheque);
        $hesabdariRow1->setPerson($cheque->getPerson());
        $hesabdariRow1->setYear($acc['year']);
        $hesabdariRow1->setBs($cheque->getAmount());
        $hesabdariRow1->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code' => 125]));
        $hesabdariRow1->setBd(0);
        $hesabdariRow1->setBid($acc['bid']);
        $hesabdariRow1->setCheque($cheque);
        $hesabdariRow1->setDes('دریافت چک شماره ' . $cheque->getNumber() . ' از ' . $cheque->getPerson()->getNikename());
        $entityManager->persist($hesabdariRow1);

        $hesabdariRow2 = new HesabdariRow;
        $hesabdariRow2->setDoc($hesabdariDoc);
        $hesabdariRow2->setCheque($cheque);
        $hesabdariRow2->setBank($cheque->getBank());
        $hesabdariRow2->setYear($acc['year']);
        $hesabdariRow2->setBs(0);
        $hesabdariRow2->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code' => 126]));
        $hesabdariRow2->setBd($cheque->getAmount());
        $hesabdariRow2->setBid($acc['bid']);
        $hesabdariRow2->setCheque($cheque);
        $hesabdariRow2->setDes('دریافت چک شماره ' . $cheque->getNumber() . ' از ' . $cheque->getPerson()->getNikename());
        $entityManager->persist($hesabdariRow2);

        $entityManager->flush();

        $log->insert(
            'بانکداری',
            'دریافت چک شماره ' . $cheque->getNumber() . ' بانک ' . $cheque->getBankoncheque() . ' از ' . $cheque->getPerson()->getNikename(),
            $this->getUser(),
            $acc['bid']->getId(),
            $hesabdariDoc
        );
        return $this->json(['result' => 'ok']);
    }

    #[Route('/api/cheque/input/get/{id}', name: 'app_cheque_input_get')]
    public function app_cheque_input_get(string $id, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, Jdate $jdate): JsonResponse
    {
        $acc = $access->hasRole('cheque');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $cheque = $entityManager->getRepository(Cheque::class)->findOneBy([
            'bid' => $acc['bid'],
            'id' => $id
        ]);

        if (!$cheque)
            throw $this->createNotFoundException('چک مورد نظر یافت نشد');
        if ($cheque->getType() !== 'input')
            throw $this->createAccessDeniedException('این چک واریزی نیست');

        return $this->json(Explore::SerializeCheque($cheque));
    }

    #[Route('/api/cheque/unreject/{id}', name: 'app_cheque_unreject')]
    public function app_cheque_unreject(string $id, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, Jdate $jdate): JsonResponse
    {
        $acc = $access->hasRole('cheque');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $cheque = $entityManager->getRepository(Cheque::class)->findOneBy([
            'bid' => $acc['bid'],
            'id' => $id
        ]);

        if (!$cheque)
            throw $this->createNotFoundException('چک مورد نظر یافت نشد');

        if (!$cheque->isRejected())
            throw $this->createAccessDeniedException('این چک برگشت نخورده است');

        $cheque->setStatus('وصول نشده');
        $cheque->setRejected(false);
        $cheque->setLocked(false);
        //حذف سند حسابداری
        $hesabdariRow = $entityManager->getRepository(HesabdariRow::class)->findBy([
            'cheque' => $cheque,
            'bid' => $acc['bid'],
        ]);
        $hesabdariDoc = null;
        $code = null;
        foreach ($hesabdariRow as $row) {
            if ($row->getDoc()->getType() == 'reject_cheque') {
                $hesabdariDoc = $row->getDoc();
                $code = $hesabdariDoc->getCode();
                break;
            }
        }
        if ($hesabdariDoc) {
            $oldRows = $hesabdariDoc->getHesabdariRows();
            foreach ($oldRows as $row) {
                $entityManager->remove($row);
            }
            //unlink log
            $logs = $hesabdariDoc->getLogs();
            foreach ($logs as $logItem) {
                $logItem->setDoc(null);
                $entityManager->persist($logItem);
            }
        }

        $log->insert(
            'بانکداری',
            'رفع برگشت چک شماره ' . $cheque->getNumber() . ' از ' . $cheque->getPerson()->getNikename() . ' و حذف سند حسابداری ' . $code,
            $this->getUser(),
            $acc['bid']->getId(),
            $hesabdariDoc
        );

        $entityManager->persist($cheque);
        $entityManager->flush();
        $entityManager->remove($hesabdariDoc);
        return $this->json(['result' => 'ok']);
    }

    #[Route('/api/cheque/input/delete/{id}', name: 'app_cheque_input_delete')]
    public function app_cheque_input_delete(string $id, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, Jdate $jdate): JsonResponse
    {
        $acc = $access->hasRole('cheque');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $cheque = $entityManager->getRepository(Cheque::class)->findOneBy([
            'bid' => $request->headers->get('activeBid'),
            'id' => $id
        ]);
        if (!$cheque) {
            throw new NotFoundHttpException('چک مورد نظر یافت نشد');
        }

        // پیدا کردن تمام ردیف‌های حسابداری مرتبط با چک
        $hesabdariRows = $entityManager->getRepository(HesabdariRow::class)->findBy([
            'cheque' => $cheque,
            'bid' => $acc['bid']
        ]);

        foreach ($hesabdariRows as $hesabdariRow) {
            $hesabdariDoc = $hesabdariRow->getDoc();
            if ($hesabdariDoc) {
                // پیدا کردن و حذف تمام ردیف‌های مرتبط با این سند
                $docRows = $entityManager->getRepository(HesabdariRow::class)->findBy(['doc' => $hesabdariDoc]);
                foreach ($docRows as $row) {
                    $entityManager->remove($row);
                }

                // به‌روزرسانی لاگ‌های مرتبط با سند
                $logs = $entityManager->getRepository(\App\Entity\Log::class)->findBy(['doc' => $hesabdariDoc]);
                foreach ($logs as $logEntry) {
                    $logEntry->setDoc(null);
                    $entityManager->persist($logEntry);
                }

                $code = $hesabdariDoc->getCode();
                $entityManager->remove($hesabdariDoc);

                $log->insert(
                    'حسابداری',
                    'حذف سند حسابداری مرتبط با چک با شماره سند ' . $code,
                    $this->getUser(),
                    $request->headers->get('activeBid')
                );
            }
        }

        // اعمال تغییرات برای حذف ردیف‌ها و اسناد
        $entityManager->flush();

        // حذف چک
        $chequeNumber = $cheque->getNumber();
        $personName = $cheque->getPerson()->getNikename();
        $entityManager->remove($cheque);
        $entityManager->flush();

        $log->insert(
            'بانکداری',
            'حذف چک شماره ' . $chequeNumber . ' از ' . $personName,
            $this->getUser(),
            $request->headers->get('activeBid')
        );

        return $this->json(['result' => 'ok']);
    }

    #[Route('/api/cheque/transfer/{id}', name: 'app_cheque_transfer')]
    public function app_cheque_transfer(string $id, SMS $SMS, PluginService $pluginService, registryMGR $registryMGR, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, Jdate $jdate): JsonResponse
    {
        $acc = $access->hasRole('cheque');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        if (!array_key_exists('personId', $params) || !array_key_exists('date', $params))
            throw $this->createNotFoundException('اطلاعات ناقص است');

        $cheque = $entityManager->getRepository(Cheque::class)->findOneBy([
            'bid' => $acc['bid'],
            'id' => $id
        ]);

        if (!$cheque)
            throw $this->createNotFoundException('چک مورد نظر یافت نشد');

        if ($cheque->isLocked())
            throw $this->createAccessDeniedException('عملیات بر روی چک قفل شده امکان‌پذیر نیست');

        $person = $entityManager->getRepository(Person::class)->findOneBy([
            'bid' => $acc['bid'],
            'id' => $params['personId']
        ]);

        if (!$person)
            throw $this->createNotFoundException('شخص گیرنده یافت نشد');

        // به‌روزرسانی اطلاعات چک
        $cheque->setTransferDate($params['date']);
        $cheque->setTransfered(true);
        $cheque->setStatus('واگذار شده');
        $cheque->setLocked(true);
        $entityManager->persist($cheque);

        // ایجاد سند حسابداری
        $hesabdariDoc = new HesabdariDoc;
        $hesabdariDoc->setBid($acc['bid']);
        $hesabdariDoc->setSubmitter($this->getUser());
        $hesabdariDoc->setYear($acc['year']);
        $hesabdariDoc->setMoney($acc['money']);
        $hesabdariDoc->setDateSubmit(time());
        $hesabdariDoc->setType('transfer_cheque');
        $hesabdariDoc->setCode($provider->getAccountingCode($acc['bid'], 'accounting'));
        $hesabdariDoc->setDate($params['date']);
        $hesabdariDoc->setDes('واگذاری چک شماره ' . $cheque->getNumber() . ' به ' . $person->getNikename() . '  ' . $params['description']);
        $hesabdariDoc->setAmount($cheque->getAmount());
        $entityManager->persist($hesabdariDoc);

        // ایجاد ردیف‌های حسابداری
        $hesabdariRow1 = new HesabdariRow;
        $hesabdariRow1->setDoc($hesabdariDoc);
        $hesabdariRow1->setCheque($cheque);
        $hesabdariRow1->setPerson($person);
        $hesabdariRow1->setYear($acc['year']);
        $hesabdariRow1->setBs(0);
        $hesabdariRow1->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code' => 125]));
        $hesabdariRow1->setBd($cheque->getAmount());
        $hesabdariRow1->setBid($acc['bid']);
        $hesabdariRow1->setDes('واگذاری چک شماره ' . $cheque->getNumber() . ' به ' . $person->getNikename() . '  ' . $params['description']);
        $entityManager->persist($hesabdariRow1);

        $hesabdariRow2 = new HesabdariRow;
        $hesabdariRow2->setDoc($hesabdariDoc);
        $hesabdariRow2->setCheque($cheque);
        $hesabdariRow2->setYear($acc['year']);
        $hesabdariRow2->setBs($cheque->getAmount());
        $hesabdariRow2->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code' => 126]));
        $hesabdariRow2->setBd(0);
        $hesabdariRow2->setBid($acc['bid']);
        $hesabdariRow2->setDes('واگذاری چک شماره ' . $cheque->getNumber() . ' به ' . $person->getNikename() . '  ' . $params['description']);
        $entityManager->persist($hesabdariRow2);

        $entityManager->flush();
        $log->insert('بانکداری', 'چک شماره ' . $cheque->getNumber() . ' به ' . $person->getNikename() . ' واگذار شد.', $this->getUser(), $request->headers->get('activeBid'), $hesabdariDoc);

        //send sms
        $params = $request->getPayload()->all();
        if (array_key_exists('sendSms', $params) && $params['sendSms'] == true && $registryMGR->get('sms', 'chequeTransfer') != ''  && $registryMGR->get('sms', 'plugAccproChequeTransfer') != '') {
            $person = $cheque->getPerson();
            if ($person && $person->getMobile() && !$pluginService->isActive('accpro', $acc['bid'])) {
                $SMS->sendByBalance(
                    [],
                    $registryMGR->get('sms', 'chequeTransfer'),
                    $person->getMobile(),
                    $acc['bid'],
                    $this->getUser(),
                    2
                );
            }
        }
        return $this->json(['result' => 'ok']);
    }

    #[Route('/api/cheque/modify/output/{id}', name: 'app_cheque_modify_output')]
    public function app_cheque_modify_output(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, Jdate $jdate, string $id = '0'): JsonResponse
    {
        $acc = $access->hasRole('cheque');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if ($id == '0') {
            $cheque = new Cheque;
            $cheque->setLocked(false);
            $cheque->setDateSubmit(time());
            $cheque->setDateStamp(time());
            $cheque->setSubmitter($this->getUser());
            $cheque->setBid($acc['bid']);
            $cheque->setMoney($acc['money']);
            $cheque->setStatus('وصول نشده');
            $cheque->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code' => 126]));
        } else {
            $cheque = $entityManager->getRepository(Cheque::class)->findOneBy(['id' => $id, 'bid' => $acc['bid']]);
            if (!$cheque)
                throw $this->createNotFoundException('cheque not found');
            if ($cheque->isRejected() || $cheque->getStatus() === 'وصول شده')
                throw $this->createAccessDeniedException('امکان ویرایش این چک وجود ندارد');
        }
        $cheque->setNumber($params['number']);
        if (!is_numeric($params['amount'])) {
            throw new \Exception('مبلغ باید عددی باشد');
        }
        $cheque->setAmount(strpos($params['amount'], '.') === false ? (int)$params['amount'] : (float)$params['amount']);
        $cheque->setType('output');
        $cheque->setBankoncheque($params['bankoncheque']);
        $cheque->setPerson($entityManager->getRepository(Person::class)->findOneBy(['id' => $params['person']['code'], 'bid' => $acc['bid']]));
        $cheque->setSayadNum($params['sayadNumber']);
        $cheque->setDate($params['date']);
        $cheque->setDes($params['description']);
        $entityManager->persist($cheque);

        // اگر چک موجود است، سند حسابداری قبلی را پیدا و به‌روزرسانی می‌کنیم
        if ($id != '0') {
            $hesabdariRow = $entityManager->getRepository(HesabdariRow::class)->findOneBy([
                'cheque' => $cheque,
                'bid' => $acc['bid']
            ]);
            $hesabdariDoc = $hesabdariRow ? $hesabdariRow->getDoc() : null;

            if ($hesabdariDoc) {
                // حذف سطرهای حسابداری قبلی
                $oldRows = $hesabdariDoc->getHesabdariRows();
                foreach ($oldRows as $row) {
                    $entityManager->remove($row);
                }
            } else {
                $hesabdariDoc = new HesabdariDoc;
            }
        } else {
            $hesabdariDoc = new HesabdariDoc;
        }

        $hesabdariDoc->setBid($acc['bid']);
        $hesabdariDoc->setSubmitter($this->getUser());
        $hesabdariDoc->setYear($acc['year']);
        $hesabdariDoc->setMoney($acc['money']);
        $hesabdariDoc->setDateSubmit(time());
        $hesabdariDoc->setType('modify_cheque_output');
        $hesabdariDoc->setCode($provider->getAccountingCode($acc['bid'], 'accounting'));
        $hesabdariDoc->setDate($params['date']);
        $hesabdariDoc->setDes($params['description']);
        $hesabdariDoc->setAmount($cheque->getAmount());
        $entityManager->persist($hesabdariDoc);

        // ایجاد سطرهای حسابداری جدید
        $hesabdariRow1 = new HesabdariRow;
        $hesabdariRow1->setDoc($hesabdariDoc);
        $hesabdariRow1->setCheque($cheque);
        $hesabdariRow1->setPerson($cheque->getPerson());
        $hesabdariRow1->setYear($acc['year']);
        $hesabdariRow1->setBs(0);
        $hesabdariRow1->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code' => 126]));
        $hesabdariRow1->setBd($cheque->getAmount());
        $hesabdariRow1->setBid($acc['bid']);
        $hesabdariRow1->setCheque($cheque);
        $hesabdariRow1->setDes('واگذاری چک شماره ' . $cheque->getNumber() . ' به ' . $cheque->getPerson()->getNikename());
        $entityManager->persist($hesabdariRow1);

        $hesabdariRow2 = new HesabdariRow;
        $hesabdariRow2->setDoc($hesabdariDoc);
        $hesabdariRow2->setCheque($cheque);
        $hesabdariRow2->setYear($acc['year']);
        $hesabdariRow2->setBs($cheque->getAmount());
        $hesabdariRow2->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code' => 125]));
        $hesabdariRow2->setBd(0);
        $hesabdariRow2->setBid($acc['bid']);
        $hesabdariRow2->setCheque($cheque);
        $hesabdariRow2->setDes('واگذاری چک شماره ' . $cheque->getNumber() . ' به ' . $cheque->getPerson()->getNikename());
        $entityManager->persist($hesabdariRow2);

        $entityManager->flush();

        $log->insert(
            'بانکداری',
            'واگذاری چک شماره ' . $cheque->getNumber() . ' بانک ' . $cheque->getBankoncheque() . ' به ' . $cheque->getPerson()->getNikename(),
            $this->getUser(),
            $acc['bid']->getId(),
            $hesabdariDoc
        );
        return $this->json(['result' => 'ok']);
    }

    #[Route('/api/cheque/output/get/{id}', name: 'app_cheque_output_get')]
    public function app_cheque_output_get(string $id, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, Jdate $jdate): JsonResponse
    {
        $acc = $access->hasRole('cheque');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $cheque = $entityManager->getRepository(Cheque::class)->findOneBy([
            'bid' => $acc['bid'],
            'id' => $id
        ]);

        if (!$cheque)
            throw $this->createNotFoundException('چک مورد نظر یافت نشد');

        if ($cheque->getType() !== 'output')
            throw $this->createAccessDeniedException('این چک واگذاری نشده است');

        return $this->json(Explore::SerializeCheque($cheque));
    }

    #[Route('/api/cheque/pass/output/{id}', name: 'app_cheque_pass_output')]
    public function app_cheque_pass_output(string $id, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, Jdate $jdate): JsonResponse
    {
        $acc = $access->hasRole('cheque');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (! array_key_exists('bank', $params) || ! array_key_exists('date', $params))
            $this->createNotFoundException();
        $cheque = $entityManager->getRepository(Cheque::class)->findOneBy([
            'bid' => $acc['bid'],
            'type' => 'output',
            'id' => $id
        ]);
        $bank = $entityManager->getRepository(BankAccount::class)->findOneBy([
            'bid' => $acc['bid'],
            'id' => $params['bank']
        ]);
        if (!$cheque || !$bank)
            throw $this->createNotFoundException();
        if ($cheque->isLocked())
            throw $this->createAccessDeniedException('cheque operation not permitted');

        //edit cheque info
        $cheque->setBank($bank);
        $cheque->setStatus('وصول');
        $cheque->setPayDate($params['date']);
        $cheque->setLocked(true);
        $entityManager->persist($cheque);

        //create cheque document
        $hesabdariDoc = new HesabdariDoc;
        $hesabdariDoc->setBid($acc['bid']);
        $hesabdariDoc->setSubmitter($this->getUser());
        $hesabdariDoc->setYear($acc['year']);
        $hesabdariDoc->setMoney($acc['money']);
        $hesabdariDoc->setDateSubmit(time());
        $hesabdariDoc->setDate($params['date']);
        $hesabdariDoc->setType('pass_cheque');
        $hesabdariDoc->setCode($provider->getAccountingCode($acc['bid'], 'accounting'));
        $hesabdariDoc->setDes($params['des']);
        $hesabdariDoc->setAmount($cheque->getAmount());
        $entityManager->persist($hesabdariDoc);

        //cheate hesabdari rows
        $hesabdariRow1 = new HesabdariRow();
        $hesabdariRow1->setDoc($hesabdariDoc);
        $hesabdariRow1->setCheque($cheque);
        $hesabdariRow1->setYear($acc['year']);
        $hesabdariRow1->setBd($cheque->getAmount());
        $hesabdariRow1->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code' => 126]));
        $hesabdariRow1->setBs(0);
        $hesabdariRow1->setBid($acc['bid']);
        $hesabdariRow1->setDes('وصول چک و برداشت از بانک');
        $entityManager->persist($hesabdariRow1);

        $hesabdariRow2 = new HesabdariRow();
        $hesabdariRow2->setDoc($hesabdariDoc);
        $hesabdariRow2->setCheque($cheque);
        $hesabdariRow2->setBank($bank);
        $hesabdariRow2->setYear($acc['year']);
        $hesabdariRow2->setBd(0);
        $hesabdariRow2->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code' => 125]));
        $hesabdariRow2->setBs($cheque->getAmount());
        $hesabdariRow2->setBid($acc['bid']);
        $hesabdariRow2->setDes('وصول چک و برداشت از بانک');
        $entityManager->persist($hesabdariRow2);
        $entityManager->flush();
        $log->insert(
            'حسابداری',
            'ثبت چک وصول شماره ' . $cheque->getNumber() . ' و ثبت برداشت از بانک ' . $bank->getName(),
            $this->getUser(),
            $acc['bid']->getId(),
            $hesabdariDoc
        );

        return $this->json([
            'result' => 'ok'
        ]);
    }
}
