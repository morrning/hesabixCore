<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\Plugin;
use App\Entity\PluginProdect;
use App\Entity\Settings;
use App\Service\Access;
use App\Service\Jdate;
use App\Service\Log;
use App\Service\PayMGR;
use App\Service\twigFunctions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PluginController extends AbstractController
{
    #[Route('/api/plugin/get/info/{id}', name: 'api_plugin_get_info')]
    public function api_plugin_get_info(string $id, Access $access, Jdate $jdate, EntityManagerInterface $entityManager, Log $log): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $item = $entityManager->getRepository(PluginProdect::class)->findOneBy([
            'code' => $id
        ]);
        return $this->json($item);
    }

    #[Route('/api/plugin/insert/{id}', name: 'api_plugin_insert')]
    public function api_plugin_insert(string $id, Log $log, twigFunctions $twigFunctions, PayMGR $payMGR, Access $access, EntityManagerInterface $entityManager): Response
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $pp = $entityManager->getRepository(PluginProdect::class)->find($id);
        if (!$pp)
            throw $this->createNotFoundException('plugin not found');
        //get system settings
        $settings = $twigFunctions->systemSettings();
        $plugin = new Plugin();
        $plugin->setBid($acc['bid']);
        $plugin->setSubmitter($this->getUser());
        $plugin->setDateSubmit(time());
        $plugin->setStatus(0);
        $plugin->setDes($pp->getName());
        $plugin->setName($pp->getCode());
        $plugin->setPrice(($pp->getPrice() * 109) / 10);
        $plugin->setDateExpire(time() + $pp->getTimestamp());
        $entityManager->persist($plugin);
        $entityManager->flush();
        $result = $payMGR->createRequest(($pp->getPrice() * 109) / 10, $this->generateUrl('api_plugin_buy_verify', ['id' => $plugin->getId()], UrlGeneratorInterface::ABSOLUTE_URL), 'خرید فضای ابری');
        if ($result['Success']) {
            $plugin->setGatePay($result['gate']);
            $plugin->setVerifyCode($result['authkey']);
            $entityManager->persist($plugin);
            $entityManager->flush();
            $entityManager->persist($plugin);
            $entityManager->flush();
            $log->insert('بازار افزونه‌ها', 'صدور فاکتور افزونه ' . $pp->getName(), $this->getUser(), $acc['bid']);
        }
        return $this->json($result);
    }

    #[Route('/api/plugin/buy/verify/{id}', name: 'api_plugin_buy_verify')]
    public function api_plugin_buy_verify(string $id, twigFunctions $twigFunctions, PayMGR $payMGR, Request $request, EntityManagerInterface $entityManager, Log $log): Response
    {
        $req = $entityManager->getRepository(Plugin::class)->find($id);
        $res = $payMGR->verify($req->getPrice(), $id, $request);
        if ($res['Success'] == false) {
            $log->insert(
                'بازار افزونه‌ها' . $req->getName(),
                'پرداخت ناموفق صورت‌حساب خرید افزونه',
                $req->getSubmitter(),
                $req->getBid()
            );
            return $this->render('buy/fail.html.twig', ['results' => $res]);
        } else {
            $req->setStatus(100);
            $req->setRefID($res['refID']);
            $req->setCardPan($res['card_pan']);
            $entityManager->persist($req);
            $entityManager->flush();
            $log->insert(
                'افزونه ' . $req->getName(),
                'افزونه جدید خریداری و فعال شد.',
                $req->getSubmitter(),
                $req->getBid()
            );
            return $this->render('buy/success.html.twig', ['req' => $req]);
        }
    }

    #[Route('/api/plugin/get/actives', name: 'api_plugin_get_actives')]
    public function api_plugin_get_actives(Access $access, Jdate $jdate, EntityManagerInterface $entityManager, Log $log): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $plugins = $entityManager->getRepository(Plugin::class)->findActivePlugins($acc['bid']);
        $temp = [];
        foreach ($plugins as $plugin) {
            $plugin->setDateExpire($jdate->jdate('Y/n/d', $plugin->getDateExpire()));
            $temp[$plugin->getName()] = $plugin;
        }
        return $this->json($temp);
    }

    #[Route('/api/plugin/get/paids', name: 'api_plugin_get_paids')]
    public function api_plugin_get_paids(Access $access, Jdate $jdate, EntityManagerInterface $entityManager, Log $log): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $plugins = $entityManager->getRepository(Plugin::class)->findBy([
            'bid' => $acc['bid'],
        ]);
        $temp = [];
        foreach ($plugins as $plugin) {
            $plugin->setDateExpire($jdate->jdate('Y/n/d', $plugin->getDateExpire()));
            $plugin->setDateSubmit($jdate->jdate('Y/n/d', $plugin->getDateSubmit()));
            $plugin->setPrice(number_format($plugin->getPrice()));

        }
        return $this->json($plugins);
    }
    #[Route('/api/plugin/get/all', name: 'api_plugin_get_all')]
    public function api_plugin_get_all(Access $access, Jdate $jdate, EntityManagerInterface $entityManager, Log $log): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $plugins = $entityManager->getRepository(PluginProdect::class)->findAll();
        return $this->json($plugins);
    }

}
