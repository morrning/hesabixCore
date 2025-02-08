<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\HesabdariDoc;
use App\Entity\Settings;
use App\Entity\SMSPays;
use App\Entity\SMSSettings;
use App\Service\Access;
use App\Service\Jdate;
use App\Service\Log;
use App\Service\Notification;
use App\Service\PayMGR;
use App\Service\PluginService;
use App\Service\Provider;
use App\Service\registryMGR;
use App\Service\SMS;
use App\Service\twigFunctions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SMSController extends AbstractController
{
    #[Route('/api/sms/save/settings', name: 'api_sms_save_settings')]
    public function api_sms_save_settings(Access $access, Log $log, Request $request, EntityManagerInterface $entityManager): Response
    {
        $acc = $access->hasRole('owner');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (array_key_exists('settings', $params))
            $params = $params['settings'];
        $settings = $entityManager->getRepository(SMSSettings::class)->findOneBy([
            'bid' => $acc['bid']
        ]);
        if (!$settings)
            $settings = new SMSSettings();
        $settings->setBid($acc['bid']);
        if (array_key_exists('sendAfterSell', $params))
            $settings->setSendAfterSell($params['sendAfterSell']);
        if (array_key_exists('sendAfterSellPayOnline', $params))
            $settings->setSendAfterSellPayOnline($params['sendAfterSellPayOnline']);
        if (array_key_exists('sendAfterBuy', $params))
            $settings->setSendAfterBuy($params['sendAfterBuy']);
        if (array_key_exists('sendAfterBuyToUser', $params))
            $settings->setSendAfterBuyToUser($params['sendAfterBuyToUser']);
        $entityManager->persist($settings);
        $entityManager->flush();
        $log->insert('سرویس پیامک', 'به روز رسانی تنظیمات', $this->getUser(), $acc['bid']);

        return $this->json(['result' => 1]);

    }

    /**
     * @throws \ReflectionException
     */
    #[Route('/api/sms/load/pays', name: 'api_sms_load_pays')]
    public function api_sms_load_pays(Jdate $jdate, Provider $provider, Access $access, Log $log, Request $request, EntityManagerInterface $entityManager): Response
    {
        $acc = $access->hasRole('owner');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $items = $entityManager->getRepository(SMSPays::class)->findBy([
            'bid' => $acc['bid']
        ], ['id' => 'DESC']);
        $res = [];
        foreach ($items as $item) {
            $temp = [];
            $temp['des'] = $item->getDes();
            $temp['submitter'] = $item->getSubmitter()->getFullName();
            $temp['dateSubmit'] = $jdate->jdate('Y/n/d H:i', $item->getDateSubmit());
            $temp['status'] = $item->getStatus();
            $temp['price'] = number_format($item->getPrice());
            $res[] = $temp;
        }
        return $this->json($res);

    }

    /**
     * @throws \ReflectionException
     */
    #[Route('/api/sms/load/settings', name: 'api_sms_load_settings')]
    public function api_sms_load_settings(Provider $provider, Access $access, Log $log, Request $request, EntityManagerInterface $entityManager): Response
    {
        $acc = $access->hasRole('owner');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $settings = $entityManager->getRepository(SMSSettings::class)->findOneBy([
            'bid' => $acc['bid']
        ]);
        if (!$settings)
            $settings = new SMSSettings();

        return $this->json($provider->Entity2Array($settings, 0));

    }
    #[Route('/api/sms/charge', name: 'api_sms_charge')]
    public function api_sms_charge(PayMGR $payMGR, Log $log, registryMGR $registryMGR, Request $request, Access $access, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('owner');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('price', $params))
            throw $this->createAccessDeniedException('price not set');

        $smsPay = new SMSPays();
        $smsPay->setBid($acc['bid']);
        $smsPay->setDateSubmit(time());
        $smsPay->setSubmitter($this->getUser());
        $smsPay->setDes('افزایش اعتبار سرویس پیامک');
        $smsPay->setPrice($params['price']);
        $smsPay->setStatus(0);
        $entityManager->persist($smsPay);
        $entityManager->flush();

        $result = $payMGR->createRequest($params['price'], $this->generateUrl('api_sms_buy_verify', ['id' => $smsPay->getId()], UrlGeneratorInterface::ABSOLUTE_URL), 'افزایش اعتبار سرویس پیامک');
        if ($result['Success']) {
            $smsPay->setVerifyCode($result['authkey']);
            $smsPay->setGatePay($result['gate']);
            $entityManager->persist($smsPay);
            $entityManager->flush();
            $log->insert('سرویس پیامک', 'صدور فاکتور شارژ سرویس پیامک', $this->getUser(), $acc['bid']);
        }
        return $this->json($result);
    }

    #[Route('/api/sms/buy/verify/{id}', name: 'api_sms_buy_verify')]
    public function api_sms_buy_verify(string $id, PayMGR $payMGR, twigFunctions $twigFunctions, Notification $notification, Request $request, EntityManagerInterface $entityManager, Log $log): Response
    {
        $req = $entityManager->getRepository(SMSPays::class)->find($id);
        $res = $payMGR->verify($req->getPrice(), $id, $request);
        if ($res['Success'] == false) {
            $log->insert('سرویس پیامک', 'پرداخت ناموفق شارژ سرویس پیامک', $this->getUser(), $req->getBid());
            return $this->render('buy/fail.html.twig', ['results' => $res]);
        } else {
            $req->setStatus(100);
            $req->setRefID($res['refID']);
            $req->setCardPan($res['card_pan']);
            $req->getBid()->setSmsCharge($req->getBid()->getSmsCharge() + ($req->getPrice() / 1.09));
            $entityManager->persist($req);
            $entityManager->flush();
            $log->insert(
                'سرویس پیامک',
                'افزایش اعتبار سرویس پیامک به مبلغ: ' . $req->getPrice() . ' ریال ',
                $req->getSubmitter(),
                $req->getBid()
            );
            $notification->insert(' سرویس پیامک شارژ شد.', '/acc/sms/panel', $req->getBid(), $req->getSubmitter());
            return $this->render('buy/success.html.twig', ['req' => $req]);
        }
    }

    /**
     * @throws \ReflectionException
     */
    #[Route('/api/sms/send/sell-invoice/{id}/{num}', name: 'api_sms_send_invoice')]
    public function api_sms_send_invoice(PluginService $pluginService, registryMGR $registryMGR, SMS $SMS, string $id, string $num, Provider $provider, Access $access, Log $log, Request $request, EntityManagerInterface $entityManager): Response
    {
        $acc = $access->hasRole('sell');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $bid = $acc['bid'];
        if ($bid->getSmsCharge() < 530)
            return $this->json(['result' => '2']);
        $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
            'id' => $id,
            'bid' => $bid,
            'type' => 'sell',
            'money' => $acc['money']
        ]);
        if (!$doc)
            return $this->json(['result' => 3]);
        $shortLink = $doc->getId();
        if ($doc->getShortlink())
            $shortLink = $doc->getShortlink();

        //find custommer
        $customer = null;
        foreach ($doc->getHesabdariRows() as $row) {
            if ($row->getPerson())
                $customer = $row->getPerson();
        }
        if ($pluginService->isActive('accpro', $acc['bid']) && $customer && $bid->getTel()) {
            return $this->json([
                'result' =>
                    $SMS->sendByBalance(
                        [$customer->getnikename(), 'sell/' . $bid->getId() . '/' . $shortLink, $bid->getName(), $bid->getTel()],
                        $registryMGR->get('sms', 'plugAccproSharefaktor'),
                        $num,
                        $bid,
                        $this->getUser(),
                        3
                    )
            ]);
        } else {
            return $this->json([
                'result' =>
                    $SMS->sendByBalance(
                        [$bid->getName(), 'sell/' . $bid->getId() . '/' . $shortLink],
                        $registryMGR->get('sms', 'sharefaktor'),
                        $num,
                        $bid,
                        $this->getUser(),
                        3
                    )
            ]);
        }


    }
}
