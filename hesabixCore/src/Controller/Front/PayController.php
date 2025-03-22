<?php

namespace App\Controller\Front;

use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Entity\HesabdariTable;
use App\Entity\PayInfoTemp;
use App\Entity\Settings;
use App\Entity\User;
use App\Entity\WalletTransaction;
use App\Entity\Year;
use App\Service\Jdate;
use App\Service\Log;
use App\Service\Notification;
use App\Service\PayMGR;
use App\Service\Provider;
use App\Service\twigFunctions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PayController extends AbstractController
{
    #[Route('/pay/sell/{id}', name: 'pay_sell')]
    public function pay_sell(string $id, PayMGR $payMGR, twigFunctions $twigFunctions, EntityManagerInterface $entityManager, Log $log): Response
    {
        $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy(['shortlink'=>$id]);
        if (!$doc)
            throw $this->createNotFoundException();

        //calculate total pays
        $totalPays = 0;
        if ($doc->getWalletTransaction())
            $totalPays += $doc->getWalletTransaction()->getAmount();
        foreach ($doc->getRelatedDocs() as $relatedDoc)
            $totalPays += $relatedDoc->getAmount();
        $amountPay = $doc->getAmount() - $totalPays;

        $tempPay = new PayInfoTemp();
        $tempPay->setBid($doc->getBid());
        $tempPay->setDateSubmit(time());
        $tempPay->setDes('پرداخت فاکتور شماره ' . $doc->getCode() . ' کسب و کار ' . $doc->getBid()->getLegalName());
        $tempPay->setPrice($amountPay);
        $tempPay->setStatus(0);
        $tempPay->setDoc($doc);
        $entityManager->persist($tempPay);
        $entityManager->flush();
        $result = $payMGR->createRequest($amountPay, $this->generateUrl('pay_sell_verify', ["id" => $tempPay->getId()], UrlGeneratorInterface::ABSOLUTE_URL), 'پرداخت فاکتور شماره ' . $doc->getCode() . ' کسب و کار ' . $doc->getBid()->getLegalName());
        if ($result['Success']) {
            $tempPay->setGatePay($result['gate']);
            $tempPay->setVerifyCode($result['authkey']);
            $entityManager->persist($tempPay);
            $entityManager->flush();
            $log->insert('کیف پول', 'ایجاد تراکنش پرداخت برای فاکتور فروش ' . $doc->getCode(), $this->getUser(), $doc->getBid());
            return $this->redirect($result['targetURL']);
        }
        return $this->render('pay/fail.html.twig', [
            'type' => 'sell',
            'doc' => $doc
        ]);
    }

    #[Route('pay/sell/verify/{id}', name: 'pay_sell_verify')]
    public function pay_sell_verify(string $id, PayMGR $payMGR, Notification $notification, Provider $provider, Jdate $jdate, Request $request, EntityManagerInterface $entityManager, Log $log): Response
    {
        $req = $entityManager->getRepository(PayInfoTemp::class)->find($id);
        if (!$req)
            throw $this->createNotFoundException('');
        $doc = $req->getDoc();
        if (!$doc)
            throw $this->createNotFoundException();

        $res = $payMGR->verify($req->getPrice(), $req->getVerifyCode(), $request);
        if ($res['Success'] == false) {
            $log->insert('کیف پول', 'خطا در پرداخت فاکتور فروش ' . $doc->getCode(), $this->getUser(), $doc->getBid());
            return $this->redirectToRoute('shortlinks_show', ['type' => 'sell', 'bid' => $doc->getBid()->getId(), 'link' => $doc->getShortlink(), 'msg' => 'fail']);
        } else {
            $req->setStatus(100);
            $req->setRefID($res['refID']);
            $req->setCardPan($res['card_pan']);
            $entityManager->persist($req);
            $entityManager->flush();
            $originalDoc = $req->getDoc();
            
            //create wallet transaction
            $wt = new WalletTransaction();
            $wt->setBid($req->getBid());
            $wt->setDes($req->getDes());
            $wt->setDateSubmit($req->getDateSubmit());
            $wt->setGatePay($req->getGatePay());
            $wt->setStatus($req->getStatus());
            $wt->setVerifyCode($req->getVerifyCode());
            $wt->setRefID($req->getRefID());
            $wt->setCardPan($req->getCardPan());
            $wt->setAmount($req->getPrice());
            $wt->setType('sell');
            $entityManager->persist($wt);
            $entityManager->flush();
            $doc->setWalletTransaction($wt);
            $entityManager->persist($doc);
            $entityManager->flush();
            $entityManager->persist($originalDoc);
            $entityManager->flush();

            //create hesabdariDoc
            $doc = new HesabdariDoc();
            $doc->setBid($wt->getBid());
            $doc->setDateSubmit(time());
            $doc->setDate($jdate->jdate('Y/n/d', time()));
            $doc->setType('walletPay');
            $doc->setMoney($wt->getBid()->getMoney());
            //get default year
            $year = $entityManager->getRepository(Year::class)->findOneBy([
                'bid' => $originalDoc->getBid(),
                'head' => true
            ]);
            $doc->setYear($year);
            $doc->setDes($wt->getDes());
            $doc->setAmount($wt->getAmount());
            $doc->setCode($provider->getAccountingCode($wt->getBid(), 'accounting'));
            $walletUser = $entityManager->getRepository(User::class)->findOneBy(['email' => 'wallet@hesabix.ir']);
            if ($walletUser)
                $doc->setSubmitter($walletUser);
            else {
                $wu = new User();
                $wu->setFullName('کیف پول');
                $wu->setEmail('wallet@hesabix.ir');
                $wu->setRoles([]);
                $wu->setActive(true);
                $entityManager->persist($wu);
                $entityManager->flush();
                $doc->setSubmitter($wu);
            }
            $entityManager->persist($doc);
            $entityManager->flush();
            $originalDoc->addRelatedDoc($doc);
            $originalDoc->setWalletTransaction($wt);
            $entityManager->persist($originalDoc);
            $entityManager->flush();

            //create rows bank
            $row = new HesabdariRow();
            $row->setBid($originalDoc->getBid());
            $row->setDoc($doc);
            $row->setBank($doc->getBid()->getWalletMatchBank());
            $row->setBd($doc->getAmount());
            $row->setBs(0);
            $row->setDes('دریافت وجه فاکتور آنلاین شماره ' . $originalDoc->getCode());
            //get table ref
            $table = $entityManager->getRepository(HesabdariTable::class)->findOneBy(['code' => 5]);
            $row->setRef($table);
            $row->setYear($year);
            $entityManager->persist($row);
            $entityManager->flush();


            //create rows person
            //get person
            $rows = $entityManager->getRepository(HesabdariRow::class)->findBy(['doc' => $originalDoc]);
            $person = null;
            foreach ($rows as $oldRow) {
                if ($oldRow->getPerson())
                    $person = $oldRow->getPerson();
            }
            $row = new HesabdariRow();
            $row->setBid($originalDoc->getBid());
            $row->setDoc($doc);
            $row->setPerson($person);
            $row->setBs($doc->getAmount());
            $row->setBd(0);
            $row->setDes('پرداخت وجه فاکتور آنلاین شماره ' . $originalDoc->getCode());
            //get table ref
            $table = $entityManager->getRepository(HesabdariTable::class)->findOneBy(['code' => 3]);
            $row->setRef($table);
            $row->setYear($year);
            $entityManager->persist($row);
            $entityManager->flush();

            $log->insert('کیف پول', 'پرداخت موفق فاکتور فروش ' . $originalDoc->getCode(), $this->getUser(), $doc->getBid());
            return $this->redirectToRoute('shortlinks_show', ['type' => 'sell', 'bid' => $originalDoc->getBid()->getId(), 'link' => $originalDoc->getId(), 'msg' => 'success']);
        }
    }
}
