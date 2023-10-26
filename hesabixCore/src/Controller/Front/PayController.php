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
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PayController extends AbstractController
{
    #[Route('/pay/sell/{id}', name: 'pay_sell')]
    public function pay_sell(String $id,EntityManagerInterface $entityManager,Log $log): Response
    {
        $doc = $entityManager->getRepository(HesabdariDoc::class)->find($id);
        if(!$doc)
            throw $this->createNotFoundException();

        //calculate total pays
        $totalPays = 0;
        if($doc->getWalletTransaction())
            $totalPays += $doc->getWalletTransaction()->getAmount();
        foreach ($doc->getRelatedDocs() as $relatedDoc)
            $totalPays += $relatedDoc->getAmount();
        $amountPay = $doc->getAmount() - $totalPays;

        //get system settings
        $settings = $entityManager->getRepository(Settings::class)->findAll()[0];
        $data = array("merchant_id" => $settings->getZarinpalMerchant(),
            "amount" => $amountPay,
            "callback_url" => $this->generateUrl('pay_sell_verify',['id'=>$doc->getId()],UrlGeneratorInterface::ABSOLUTE_URL),
            "description" => 'پرداخت فاکتور شماره ' . $doc->getCode() . ' کسب و کار ' .$doc->getBid()->getLegalName(),
        );
        $jsonData = json_encode($data);
        $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/request.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));

        $result = curl_exec($ch);
        $err = curl_error($ch);
        $result = json_decode($result, true, JSON_PRETTY_PRINT);
        curl_close($ch);
        if ($err) {
            throw $this->createAccessDeniedException($err);
        } else {
            if (empty($result['errors'])) {
                if ($result['data']['code'] == 100) {
                    $tempPay = new PayInfoTemp();
                    $tempPay->setBid($doc->getBid());
                    $tempPay->setDateSubmit(time());
                    $tempPay->setDes('پرداخت فاکتور شماره ' . $doc->getCode() . ' کسب و کار ' .$doc->getBid()->getLegalName());
                    $tempPay->setPrice($amountPay);
                    $tempPay->setStatus(0);
                    $tempPay->setDoc($doc);
                    $tempPay->setVerifyCode($result['data']['authority']);
                    $tempPay->setGatePay('zarinpal');
                    $entityManager->persist($tempPay);
                    $entityManager->flush();
                    $log->insert('کیف پول','ایجاد تراکنش پرداخت برای فاکتور فروش ' . $doc->getCode() ,$this->getUser(),$doc->getBid());
                    return $this->redirect('https://www.zarinpal.com/pg/StartPay/' . $result['data']["authority"]);
                }
            }
        }
        return $this->render('pay/fail.html.twig',[
            'type'=>'sell',
            'doc'=>$doc
        ]);
    }

    #[Route('pay/sell/verify/{id}', name: 'pay_sell_verify')]
    public function pay_sell_verify(String $id, Notification $notification,Provider $provider,Jdate $jdate,Request $request,EntityManagerInterface $entityManager,Log $log): Response
    {
        $doc = $entityManager->getRepository(HesabdariDoc::class)->find($id);
        if(!$doc)
            throw $this->createNotFoundException();

        $Authority = $request->get('Authority');
        $status = $request->get('Status');
        $req = $entityManager->getRepository(PayInfoTemp::class)->findOneBy(['verifyCode'=>$Authority]);
        //get system settings
        $settings = $entityManager->getRepository(Settings::class)->findAll()[0];
        $data = array("merchant_id" => $settings->getZarinpalMerchant(), "authority" => $Authority, "amount" => $req->getPrice());
        $jsonData = json_encode($data);
        $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/verify.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));

        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        $result = json_decode($result, true);

        //-----------------------------------
        $originalDoc = $req->getDoc();
        //-----------------------------------
        if ($err) {
            $log->insert('کیف پول','خطا در پرداخت فاکتور فروش ' . $doc->getCode() ,$this->getUser(),$doc->getBid());
            return $this->redirectToRoute('shortlinks_show',['type'=>'sell','bid'=>$originalDoc->getBid()->getId(),'link'=>$originalDoc->getId(),'msg'=>'fail']);
        } else {
            if(array_key_exists('code',$result['data'])){
                if ($result['data']['code'] == 100) {
                    $req->setStatus(100);
                    $req->setRefID($result['data']['ref_id']);
                    $req->setCardPan($result['data']['card_pan']);
                    $entityManager->persist($req);
                    $entityManager->flush();

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
                    $doc->setDate($jdate->jdate('Y/n/d',time()));
                    $doc->setType('walletPay');
                    $doc->setMoney($wt->getBid()->getMoney());
                    //get default year
                    $year = $entityManager->getRepository(Year::class)->findOneBy([
                        'bid'=>$originalDoc->getBid(),
                        'head'=>true
                    ]);
                    $doc->setYear($year);
                    $doc->setDes($wt->getDes());
                    $doc->setAmount($wt->getAmount());
                    $doc->setCode($provider->getAccountingCode($wt->getBid(),'accounting'));
                    $walletUser = $entityManager->getRepository(User::class)->findOneBy(['email'=>'wallet@hesabix.ir']);
                    if($walletUser)
                        $doc->setSubmitter($walletUser);
                    else{
                        $wu = new User();
                        $wu->setFullName('کیف پول حسابیکس');
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
                    $table = $entityManager->getRepository(HesabdariTable::class)->findOneBy(['code'=>5]);
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
                    $table = $entityManager->getRepository(HesabdariTable::class)->findOneBy(['code'=>3]);
                    $row->setRef($table);
                    $row->setYear($year);
                    $entityManager->persist($row);
                    $entityManager->flush();

                    $log->insert('کیف پول','پرداخت موفق فاکتور فروش ' . $originalDoc->getCode() ,$this->getUser(),$doc->getBid());
                    return $this->redirectToRoute('shortlinks_show',['type'=>'sell','bid'=>$originalDoc->getBid()->getId(),'link'=>$originalDoc->getId(),'msg'=>'success']);
                }
            }
            $log->insert('کیف پول','خطا در پرداخت فاکتور فروش ' . $originalDoc->getCode() ,$this->getUser(),$doc->getBid());
            return $this->redirectToRoute('shortlinks_show',['type'=>'sell','bid'=>$originalDoc->getBid()->getId(),'link'=>$originalDoc->getId(),'msg'=>'fail']);
        }
    }
}
