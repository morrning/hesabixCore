<?php

namespace App\Controller\Plugins;

use App\Entity\Business;
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
use App\Service\Explore;
use App\Service\Jdate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PlugRepserviceController extends AbstractController
{
    #[Route('/p/rep/{bid}/{sharelink}', name: 'app_plug_repservice_order_view_front')]
    public function app_plug_repservice_order_view_front(string $bid,string $sharelink, EntityManagerInterface $entityManagerInterface): Response
    {
        $bid = $entityManagerInterface->getRepository(Business::class)->find($bid);
        if(!$bid) throw $this->createNotFoundException();

        $order = $entityManagerInterface->getRepository(PlugRepserviceOrder::class)->findOneBy([
            'bid' => $bid,
            'shortlink' => $sharelink
        ]);
        return $this->render('repservice/view.html.twig',[
            'order'=>$order,
            'bid'=>$bid,
            'person'=>$order->getPerson()
        ]);
    }

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
            !array_key_exists('motaleghat', $params) ||
            !array_key_exists('color', $params) ||
            !array_key_exists('model', $params) ||
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
        $order->setModel($params['model']);
        $order->setColor($params['color']);
        $order->setDes($params['des']);
        $order->setMotaleghat($params['motaleghat']);
        $order->setPelak($params['pelak']);
        $order->setSerial($params['serial']);
        $order->setState($entityManagerInterface->getRepository(PlugRepserviceOrderState::class)->findOneBy(['code' => 'get']));
        $entityManagerInterface->persist($order);
        $entityManagerInterface->flush();
        $log->insert('افزونه تعمیرکاران', ' رسید دریافت کالا با نام  ' . $person->getNikename() . ' افزوده/ویرایش شد.', $this->getUser(), $acc['bid']->getId());

        if (array_key_exists('sms', $params)) {
            if ($params['sms'] == true) {
                //going to send sms
                $smsres = $sms->sendByBalance(
                    [
                        $person->getNikename(),
                        $order->getCode(),
                        $acc['bid']->getName(),
                        $acc['bid']->getId() . '/' . $order->getShortlink()
                    ],
                    $registryMGR->get('sms', 'plugRepserviceStateGet'),
                    $person->getMobile(),
                    $acc['bid'],
                    $this->getUser(),
                    1
                );
                if ($smsres == 2) {
                    return $this->json([
                        'code' => 11,
                        'data' => '',
                        'message' => 'operation success but sms not send'
                    ]);
                }
            }
        }

        return $this->json($extractor->operationSuccess());
    }

    #[Route('/api/plug/repservice/order/state/change', name: 'app_plug_repservice_order_state_change')]
    public function app_plug_repservice_order_state_change(Jdate $jdate,Provider $provider, registryMGR $registryMGR, SMS $sms, Log $log, EntityManagerInterface $entityManagerInterface, Access $access, Request $request, Extractor $extractor): JsonResponse
    {
        $acc = $access->hasRole('plugRepservice');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (
            !array_key_exists('code', $params) ||
            !array_key_exists('state', $params) ||
            !array_key_exists('sms', $params)
        )
            return $this->json($extractor->paramsNotSend());

        $order = $entityManagerInterface->getRepository(PlugRepserviceOrder::class)->findOneBy([
            'bid' => $acc['bid'],
            'code' => $params['code']
        ]);
        if (!$order) {
            return $this->json($extractor->notFound());
        }

        //find state 
        $state = $entityManagerInterface->getRepository(PlugRepserviceOrderState::class)->findOneBy([
            'code' => $params['state']['code']
        ]);
        if (!$state) {
            return $this->json($extractor->notFound());
        }
        $order->setState($state);
        //set dateout if proccess finish
        if($state->getCode() == 'getback'){
            $order->setDateOut($jdate->jdate('Y-n-d',time()));
        }
        $entityManagerInterface->persist($order);
        $entityManagerInterface->flush();
        $log->insert('افزونه تعمیرکاران', ' وضعیت کالا با کد  ' . $order->getCode() . ' به ' . $state->getLabel() . ' تغییر یافت. ', $this->getUser(), $acc['bid']->getId());

        if (array_key_exists('sms', $params)) {
            //get state sms code
            if($params['state']['code'] == 'get') $smsPattern = $registryMGR->get('sms', 'plugRepserviceStateGet');
            elseif($params['state']['code'] == 'repaired') $smsPattern = $registryMGR->get('sms', 'plugRepserviceStateRepaired');
            elseif($params['state']['code'] == 'unrepired') $smsPattern = $registryMGR->get('sms', 'plugRepserviceStateUnrepired');
            else  $smsPattern = $registryMGR->get('sms', 'plugRepserviceStateGetback');
            if ($params['sms'] == true) {
                //going to send sms
                $smsres = $sms->sendByBalance(
                    [
                        $order->getPerson()->getNikename(),
                        $order->getCode(),
                        $acc['bid']->getName(),
                        $acc['bid']->getId() . '/' . $order->getShortlink()
                    ],
                    $smsPattern,
                    $order->getPerson()->getMobile(),
                    $acc['bid'],
                    $this->getUser(),
                    1
                );
                if ($smsres == 2) {
                    return $this->json([
                        'code' => 11,
                        'data' => '',
                        'message' => 'operation success but sms not send'
                    ]);
                }
            }
        }

        return $this->json($extractor->operationSuccess());
    }

    #[Route('/api/plug/repservice/order/list', name: 'app_plug_repservice_order_list')]
    public function app_plug_repservice_order_list(Provider $provider, registryMGR $registryMGR, SMS $sms, Log $log, EntityManagerInterface $entityManagerInterface, Access $access, Request $request, Extractor $extractor): JsonResponse
    {
        $acc = $access->hasRole('plugRepservice');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        $orders = $entityManagerInterface->getRepository(PlugRepserviceOrder::class)->findBy([
            'bid' => $acc['bid']
        ],['code'=>'DESC']);
        return $this->json($this->ExploreOrders($orders));
    }

    #[Route('/api/plug/repservice/order/state/list', name: 'app_plug_repservice_order_state_list')]
    public function app_plug_repservice_order_state_list(Provider $provider, registryMGR $registryMGR, SMS $sms, Log $log, EntityManagerInterface $entityManagerInterface, Access $access, Request $request, Extractor $extractor): JsonResponse
    {
        $acc = $access->hasRole('plugRepservice');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $items = $entityManagerInterface->getRepository(PlugRepserviceOrderState::class)->findAll();
        $res = [];
        foreach ($items as $item) {
            $res[] = [
                'code' => $item->getCode(),
                'label' => $item->getLabel(),
                'checked'=>false
            ];
        }
        return $this->json($res);
    }

    #[Route('/api/repservice/order/info/{code}', name: 'app_plug_repservice_order_info')]
    public function app_plug_repservice_order_info(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('plugRepservice');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $item = $entityManager->getRepository(PlugRepserviceOrder::class)->findOneBy(['bid' => $acc['bid'], 'code' => $code]);
        if (!$item)
            throw $this->createNotFoundException();
    
        return $this->json($this->ExploreOrder($item));
    }

    #[Route('/api/repservice/order/remove/{code}', name: 'app_plug_repservice_order_remove')]
    public function app_plug_repservice_order_remove(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('plugRepservice');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $item = $entityManager->getRepository(PlugRepserviceOrder::class)->findOneBy(['bid' => $acc['bid'], 'code' => $code]);
        if (!$item)
            throw $this->createNotFoundException();
        $code = $item->getCode();
        $entityManager->remove($item);
        $log->insert('افزونه تعمیرکاران', 'درخواست با شماره قبض' . $code . 'حذف شد.', $this->getUser(), $acc['bid']->getId());
        return $this->json(['result' => 1]);
    }

    private function ExploreOrder(PlugRepserviceOrder $item)
    {
        $temp = [
            'id' => $item->getId(),
            'update' => $item->getCode(),
            'code' => $item->getCode(),
            'person' => Explore::ExplorePerson($item->getPerson()),
            'commodity' => Explore::ExploreCommodity($item->getCommodity()),
            'des' => $item->getDes(),
            'pelak' => $item->getPelak(),
            'serial' => $item->getSerial(),
            'motaleghat' => $item->getMotaleghat(),
            'date' => $item->getDate(),
            'shortLink' => $item->getShortlink(),
            'state' => [
                'code' => $item->getState()->getCode(),
                'label' => $item->getState()->getLabel()
            ],
            'dateOut'=>$item->getDateOut(),
            'model'=>$item->getModel(),
            'color'=>$item->getColor(),
            'sms'=>true,
        ];
        if($item->getPerson()->getMobile() == null || $item->getPerson()->getMobile() == ''){
            $temp['sms'] = false;
        }

        return $temp;
    }

    private function ExploreOrders(array $items)
    {
        $res = [];
        foreach ($items as $item) {
            $res[] = $this->ExploreOrder($item);
        }
        return $res;
    }
}
