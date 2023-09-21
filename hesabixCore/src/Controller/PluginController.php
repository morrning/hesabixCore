<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\Plugin;
use App\Entity\PluginProdect;
use App\Service\Access;
use App\Service\Jdate;
use App\Service\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PluginController extends AbstractController
{
    #[Route('/api/plugin/get/info/{id}', name: 'api_plugin_get_info')]
    public function api_plugin_get_info(String $id,Access $access, Jdate $jdate, EntityManagerInterface $entityManager,Log $log): JsonResponse
    {
        $acc = $access->hasRole('join');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $item = $entityManager->getRepository(PluginProdect::class)->findOneBy([
            'code'=>$id
        ]);
        return $this->json($item);
    }

    #[Route('/api/plugin/insert/{id}', name: 'api_plugin_insert')]
    public function api_plugin_insert(String $id,Access $access,EntityManagerInterface $entityManager): Response
    {
        $acc = $access->hasRole('join');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $pp = $entityManager->getRepository(PluginProdect::class)->find($id);
        if(!$pp)
            throw $this->createNotFoundException('plugin not found');
        $data = array("merchant_id" => "a7804652-1fb9-4b43-911c-0a1046e61be1",
            "amount" => ($pp->getPrice() * 109)/10,
            "callback_url" => "https://hesabix.ir/api/plugin/buy/verify",
            "description" => $pp->getName(),
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
                    $plugin = new Plugin();
                    $plugin->setBid($acc['bid']);
                    $plugin->setSubmitter($this->getUser());
                    $plugin->setDateSubmit(time());
                    $plugin->setGatePay('zarinpal');
                    $plugin->setVerifyCode($result['data']['authority']);
                    $plugin->setStatus(0);
                    $plugin->setDes($pp->getName());
                    $plugin->setName($pp->getCode());
                    $plugin->setPrice(($pp->getPrice() * 109)/10);
                    $plugin->setDateExpire(time() + $pp->getTimestamp());
                    $entityManager->persist($plugin);
                    $entityManager->flush();
                    return $this->json([
                       'authority'=> $result['data']["authority"]
                    ]);
                }
            } else {
                throw $this->createAccessDeniedException();
            }
        }
    }
    #[Route('/api/plugin/buy/verify', name: 'api_plugin_buy_verify')]
    public function api_plugin_buy_verify(\Symfony\Component\HttpFoundation\Request $request,EntityManagerInterface $entityManager,Log $log): Response
    {
        $Authority = $request->get('Authority');
        $status = $request->get('Status');
        $req = $entityManager->getRepository(Plugin::class)->findOneBy(['verifyCode'=>$Authority]);
        $data = array("merchant_id" => "a7804652-1fb9-4b43-911c-0a1046e61be1", "authority" => $Authority, "amount" => $req->getPrice());
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

        //-----------------------------------
        if ($err) {
            return $this->render('buy/fail.html.twig', ['results'=>$result]);
        } else {
            if(array_key_exists('code',$result['data'])){
                if ($result['data']['code'] == 100) {
                    $req->setStatus(100);
                    $req->setRefID($result['data']['ref_id']);
                    $req->setCardPan($result['data']['card_pan']);
                    $entityManager->persist($req);
                    $entityManager->flush();
                    $log->insert(
                        'افزونه ' . $req->getName(),
                        'افزونه جدید خریداری و فعال شد.',
                        $req->getSubmitter(),
                        $req->getBid()
                    );
                    return $this->render('buy/success.html.twig',['req'=>$req]);
                }
            }
            return $this->render('buy/fail.html.twig', ['results'=>$result]);
        }
    }

    #[Route('/api/plugin/get/actives', name: 'api_plugin_get_actives')]
    public function api_plugin_get_actives(Access $access, Jdate $jdate, EntityManagerInterface $entityManager,Log $log): JsonResponse
    {
        $acc = $access->hasRole('join');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $plugins = $entityManager->getRepository(Plugin::class)->findActivePlugins($acc['bid']);
        $temp = [];
        foreach ($plugins as $plugin){
            $plugin->setDateExpire($jdate->jdate('Y/n/d',$plugin->getDateExpire()));
            $temp[$plugin->getName()] = $plugin;
        }
        return $this->json($temp);
    }

    #[Route('/api/plugin/get/paids', name: 'api_plugin_get_paids')]
    public function api_plugin_get_paids(Access $access, Jdate $jdate, EntityManagerInterface $entityManager,Log $log): JsonResponse
    {
        $acc = $access->hasRole('join');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $plugins = $entityManager->getRepository(Plugin::class)->findBy([
            'bid'=>$acc['bid'],
        ]);
        $temp = [];
        foreach ($plugins as $plugin){
            $plugin->setDateExpire($jdate->jdate('Y/n/d',$plugin->getDateExpire()));
            $plugin->setDateSubmit($jdate->jdate('Y/n/d',$plugin->getDateSubmit()));
            $plugin->setPrice(number_format($plugin->getPrice()));

        }
        return $this->json($plugins);
    }
    #[Route('/api/plugin/get/all', name: 'api_plugin_get_all')]
    public function api_plugin_get_all(Access $access, Jdate $jdate, EntityManagerInterface $entityManager,Log $log): JsonResponse
    {
        $acc = $access->hasRole('join');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $plugins = $entityManager->getRepository(PluginProdect::class)->findAll();
        return $this->json($plugins);
    }

}
