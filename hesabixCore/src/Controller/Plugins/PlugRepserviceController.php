<?php

namespace App\Controller\Plugins;

use App\Service\Log;
use App\Service\SMS;
use App\Entity\Person;
use App\Service\Access;
use App\Entity\Commodity;
use App\Service\Provider;
use App\Service\Extractor;
use App\Service\registryMGR;
use App\Entity\PlugRepserviceOrder;
use App\Entity\PlugRepserviceOrderState;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PlugRepserviceController extends AbstractController
{
    #[Route('/api/plug/repservice/order/mod', name: 'app_plug_repservice_order_mod')]
    public function app_plug_repservice_order_mod(Provider $provider, registryMGR $registryMGR, SMS $sms, Log $log, EntityManagerInterface $entityManagerInterface, Access $access, Request $request, Extractor $extractor): JsonResponse
    {
        $acc = $access->hasRole('plugRepservice');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (
            !array_key_exists('commodity', $params) ||
            !array_key_exists('des', $params) ||
            !array_key_exists('pelak', $params) ||
            !array_key_exists('person', $params) ||
            !array_key_exists('serial', $params) ||
            !array_key_exists('date', $params)
        )
            return $this->json($extractor->paramsNotSend());

        //find person and commodity
        $person = $entityManagerInterface->getRepository(Person::class)->findOneBy([
            'bid' => $acc['bid'],
            'code' => $params['person']['code']
        ]);

        $commodity = $entityManagerInterface->getRepository(Commodity::class)->findOneBy([
            'bid' => $acc['bid'],
            'code' => $params['commodity']['code']
        ]);
        if (!$person || !$commodity) return $this->json($extractor->paramsNotSend());

        if ($params['update'] == '') {
            $order = new PlugRepserviceOrder();
            $order->setBid($acc['bid']);
            $order->setcode($provider->getAccountingCode($acc['bid'], 'PlugRepservice'));
            $order->setShortlink($provider->RandomString(6) . time());
        } else {
            $order = $entityManagerInterface->getRepository(PlugRepserviceOrder::class)->findOneBy([
                'bid' => $acc['bid'],
                'code' => $params['update']
            ]);
            if (!$order)
                return $this->json($extractor->notFound());
        }
        $order->setDateSubmit(time());
        $order->setSubmitter($this->getUser());
        $order->setCommodity($commodity);
        $order->setPerson($person);
        $order->setDate($params['date']);
        $order->setDes($params['des']);
        $order->setPelak($params['pelak']);
        $order->setSerial($params['serial']);
        $order->setState($entityManagerInterface->getRepository(PlugRepserviceOrderState::class)->findOneBy(['code' => 'get']));
        $entityManagerInterface->persist($order);
        $entityManagerInterface->flush();
        $log->insert('افزونه تعمیرکاران', ' رسید دریافت کالا با نام  ' . $person->getNikename() . ' افزوده/ویرایش شد.', $this->getUser(), $acc['bid']->getId());

        if (array_key_exists('sms', $params)) {
            if ($params['sms'] == true) {
                //going to send sms
                $sms->send(
                    [
                        $person->getNikename(),
                        $order->getCode(),
                        $acc['bid']->getName(),
                        $acc['bid']->getId() . '/' . $order->getShortlink()
                    ],
                    $registryMGR->get('sms', 'plugRepserviceStateGet'),
                    $person->getMobile()
                );
            }
        }
        return $this->json($extractor->operationSuccess());
    }
}
