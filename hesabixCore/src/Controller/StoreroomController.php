<?php

namespace App\Controller;

use App\Entity\Commodity;
use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Entity\Person;
use App\Service\Explore;
use App\Service\Extractor;
use App\Service\Printers;
use App\Service\Provider;
use App\Entity\Storeroom;
use App\Entity\StoreroomItem;
use App\Entity\StoreroomTicket;
use App\Entity\StoreroomTransferType;
use App\Service\Access;
use App\Service\Log;
use App\Service\PluginService;
use App\Service\registryMGR;
use App\Service\SMS;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StoreroomController extends AbstractController
{
    #[Route('/api/storeroom/list/{type}', name: 'app_storeroom_list')]
    public function app_storeroom_list(Extractor $extractor, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, string $type = 'active'): JsonResponse
    {
        $acc = $access->hasRole('store');
        if (!$acc)
            throw $this->createAccessDeniedException();
        if ($type == 'active')
            $items = $entityManager->getRepository(Storeroom::class)->findBy([
                'bid' => $acc['bid'],
                'active' => true
            ]);
        else
            $items = $entityManager->getRepository(Storeroom::class)->findBy([
                'bid' => $acc['bid'],
            ]);

        return $this->json(
            $extractor->operationSuccess(
                Explore::ExploreStoreRooms(
                    $items
                )
            )
        );
    }

    #[Route('/api/storeroom/mod/{code}', name: 'app_storeroom_mod')]
    public function app_storeroom_mod(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('store');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('name', $params))
            return $this->json(['result' => -1]);
        if (count_chars(trim($params['name'])) == 0)
            return $this->json(['result' => 3]);
        if ($code == 0) {
            $data = $entityManager->getRepository(Storeroom::class)->findOneBy([
                'name' => $params['name'],
                'bid' => $acc['bid']
            ]);
            //check exist before
            if ($data)
                return $this->json(['result' => 2]);
            $data = new Storeroom();
            $data->setBid($acc['bid']);
        } else {
            $data = $entityManager->getRepository(Storeroom::class)->findOneBy([
                'bid' => $acc['bid'],
                'id' => $code
            ]);
            if (!$data)
                throw $this->createNotFoundException();
        }
        $data->setName($params['name']);
        $data->setAdr($params['adr']);
        $data->setManager($params['manager']);
        $data->setTel($params['tel']);
        if ($params['active'] == 'true')
            $data->setActive(true);
        else
            $data->setActive(false);
        $entityManager->persist($data);
        $entityManager->flush();
        $log->insert('انبارداری', 'انبار ' . $params['name'] . ' افزوده/ویرایش شد.', $this->getUser(), $acc['bid']);
        return $this->json(['result' => 1]);
    }

    /**
     * @throws ReflectionException
     */
    #[Route('/api/storeroom/info/{code}', name: 'app_storeroom_info')]
    public function app_storeroom_info($code, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('store');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $data = $entityManager->getRepository(Storeroom::class)->findOneBy([
            'bid' => $acc['bid'],
            'id' => $code
        ]);
        return $this->json($provider->Entity2Array($data, 0));
    }

    /**
     * @throws ReflectionException
     */
    #[Route('/api/storeroom/docs/get', name: 'app_storeroom_get_docs')]
    public function app_storeroom_get_docs(Provider $provider,Extractor $extractor, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('store');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $buys = $entityManager->getRepository(HesabdariDoc::class)->findBy([
            'bid' => $acc['bid'],
            'type' => 'buy',
            'money' => $acc['money']
        ]);
        $buysForExport = [];
        foreach ($buys as $buy) {
            $temp = $provider->Entity2Array($buy, 0);
            $person = $this->getPerson($buy);
            $temp['person'] = Explore::ExplorePerson($person);
            $temp['person']['des'] = ' # ' . $person->getCode() . ' ' . $person->getNikename();
            $temp['commodities'] = $this->getCommodities($buy, $provider);
            //check storeroom exist
            $this->calcStoreRemaining($temp, $buy, $entityManager);
            $temp['des'] = 'فاکتور خرید شماره # ' . $buy->getCode();
            if (array_key_exists('storeroomComplete', $temp))
                if (!$temp['storeroomComplete']) {
                    $buysForExport[] = $temp;
                }
        }

        $sells = $entityManager->getRepository(HesabdariDoc::class)->findBy([
            'bid' => $acc['bid'],
            'type' => 'sell',
            'money' => $acc['money']
        ]);
        $sellsForExport = [];
        foreach ($sells as $sell) {
            $temp = $provider->Entity2Array($sell, 0);
            $person = $this->getPerson($sell);
            $temp['person'] = Explore::ExplorePerson($person);
            $temp['person']['des'] = ' # ' . $person->getCode() . ' ' . $person->getNikename();
            $temp['commodities'] = $this->getCommodities($sell, $provider);
            //check storeroom exist
            $this->calcStoreRemaining($temp, $sell, $entityManager);
            $temp['des'] = 'فاکتور فروش شماره # ' . $sell->getCode();
            if (array_key_exists('storeroomComplete', $temp))
                if (!$temp['storeroomComplete']) {
                    $sellsForExport[] = $temp;
                }
        }

        $rfsells = $entityManager->getRepository(HesabdariDoc::class)->findBy([
            'bid' => $acc['bid'],
            'type' => 'rfsell',
            'money' => $acc['money']
        ]);
        $rfsellsForExport = [];
        foreach ($rfsells as $sell) {
            $temp = $provider->Entity2Array($sell, 0);
            $person = $this->getPerson($sell);
            $temp['person'] = Explore::ExplorePerson($person);
            $temp['person']['des'] = ' # ' . $person->getCode() . ' ' . $person->getNikename();
            $temp['commodities'] = $this->getCommodities($sell, $provider);
            //check storeroom exist
            $this->calcStoreRemaining($temp, $sell, $entityManager);
            $temp['des'] = 'فاکتور برگشت از فروش شماره # ' . $sell->getCode();
            if (array_key_exists('storeroomComplete', $temp))
                if (!$temp['storeroomComplete']) {
                    $rfsellsForExport[] = $temp;
                }
        }

        $rfbuys = $entityManager->getRepository(HesabdariDoc::class)->findBy([
            'bid' => $acc['bid'],
            'type' => 'rfbuy',
            'money' => $acc['money']
        ]);
        $rfbuysForExport = [];
        foreach ($rfbuys as $buy) {
            $temp = $provider->Entity2Array($buy, 0);
            $person = $this->getPerson($buy);
            $temp['person'] = Explore::ExplorePerson($person);
            $temp['person']['des'] = ' # ' . $person->getCode() . ' ' . $person->getNikename();
            $temp['commodities'] = $this->getCommodities($buy, $provider);
            //check storeroom exist
            $this->calcStoreRemaining($temp, $buy, $entityManager);
            $temp['des'] = 'فاکتور برگشت از خرید شماره # ' . $buy->getCode();
            if (array_key_exists('storeroomComplete', $temp))
                if (!$temp['storeroomComplete']) {
                    $rfbuysForExport[] = $temp;
                }
        }
        return $this->json([
            'buys' => $buysForExport,
            'sells' => $sellsForExport,
            'rfsells' => $rfsellsForExport,
            'rfbuys' => $rfbuysForExport
        ]);
    }

    private function getPerson(HesabdariDoc $doc): Person|bool
    {
        foreach ($doc->getHesabdariRows() as $row) {
            if ($row->getPerson())
                return $row->getPerson();
        }
        return false;
    }

    private function getCommodities(HesabdariDoc $doc, Provider $provider): array
    {
        $res = [];
        foreach ($doc->getHesabdariRows() as $row) {
            if ($row->getCommodity()) {
                $arrayRow = $provider->Entity2ArrayJustIncludes($row->getCommodity(), ['getCode', 'getName']);
                $arrayRow['commdityCount'] = $row->getCommdityCount();
                $res[] = $arrayRow;
            }
        }
        return $res;
    }
    /**
     * @throws ReflectionException
     */
    #[Route('/api/storeroom/doc/get/info/{id}', name: 'app_storeroom_get_doc_info')]
    public function app_storeroom_get_doc_info(string $id, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('store');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
            'bid' => $acc['bid'],
            'code' => $id,
            'money' => $acc['money']
        ]);
        if (!$doc)
            throw $this->createNotFoundException('سند یافت نشد.');
        if ($doc->getType() == 'buy')
            $doc->setDes('فاکتور خرید شماره #' . $doc->getCode());
        elseif ($doc->getType() == 'sell')
            $doc->setDes('فاکتور فروش شماره #' . $doc->getCode());

        //find person
        $rows = $entityManager->getRepository(HesabdariRow::class)->findBy([
            'doc' => $doc
        ]);
        $person = null;
        $commodities = [];
        foreach ($rows as $row) {
            if ($row->getPerson()) {
                $person = $row->getPerson();
                break;
            } elseif ($row->getCommodity()) {
                if (!$row->getCommodity()->isKhadamat())
                    $commodities[] = $row;
            }
        }
        foreach ($commodities as $commodity) {
            if (is_string($commodity->getCommodity()->getUnit())) {
                $commodity->getCommodity()->setUnit($commodity->getCommodity()->getUnit());
            } else {
                $commodity->getCommodity()->setUnit($commodity->getCommodity()->getUnit()->getName());
            }
        }
        $res = $provider->Entity2Array($doc, 0);
        $res['person'] = $provider->Entity2Array($person, 0);
        $res['person']['des'] = ' # ' . $person->getCode() . ' ' . $person->getNikename();
        $res['commodities'] = $provider->ArrayEntity2Array($commodities, 1, ['doc', 'bid', 'year']);
        //calculate rows data
        $this->calcStoreRemaining($res, $doc, $entityManager);
        return $this->json($res);
    }

    private function calcStoreRemaining(array &$ref, HesabdariDoc $doc, EntityManagerInterface $entityManager)
    {
        $tickets = $entityManager->getRepository(StoreroomTicket::class)->findBy(['doc' => $doc]);

        if (count($tickets) == 0) {
            $ref['storeroomComplete'] = false;
            foreach ($ref['commodities'] as $key => $commodity) {
                $ref['commodities'][$key]['countBefore'] = 0;
                $ref['commodities'][$key]['hesabdariCount'] = $commodity['commdityCount'];
                $ref['commodities'][$key]['remain'] = $ref['commodities'][$key]['hesabdariCount'];
            }
        } else {
            $ref['storeroomComplete'] = true;
            foreach ($tickets as $ticket) {
                $rows = $entityManager->getRepository(StoreroomItem::class)->findBy(['ticket' => $ticket]);
                foreach ($rows as $key => $row) {
                    $comRows = $entityManager->getRepository(HesabdariRow::class)->findBy(['doc' => $doc]);
                    foreach ($comRows as $comRow) {
                        if ($comRow->getCommodity()) {
                            if ($comRow->getCommodity() === $row->getCommodity()) {
                                if (array_key_exists('countBefore', $ref['commodities'][$key])) {
                                    $ref['commodities'][$key]['countBefore'] += $row->getCount();
                                    $ref['commodities'][$key]['hesabdariCount'] = $comRow->getCommdityCount();
                                } else {
                                    $ref['commodities'][$key]['countBefore'] = $row->getCount();
                                    $ref['commodities'][$key]['hesabdariCount'] = $comRow->getCommdityCount();
                                }
                            }
                        }
                    }
                    $ref['commodities'][$key]['remain'] = $ref['commodities'][$key]['hesabdariCount'] - $ref['commodities'][$key]['countBefore'];
                    $ref['commodities'][$key]['commodityComplete'] = true;
                    if ($ref['commodities'][$key]['remain'] != 0) {
                        $ref['commodities'][$key]['commodityComplete'] = false;
                    }
                }
            }
            foreach ($tickets as $ticket) {
                $rows = $entityManager->getRepository(StoreroomItem::class)->findBy(['ticket' => $ticket]);
                $ref['storeroomComplete'] = true;
                foreach ($rows as $key => $row) {
                    if (!$ref['commodities'][$key]['commodityComplete']) {
                        $ref['storeroomComplete'] = false;
                    }
                }
            }
        }
    }
    #[Route('/api/storeroom/transfertype/list', name: 'app_storeroom_get_transfertype_list')]
    public function app_storeroom_get_transfertype_list(PluginService $pluginService, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('store');
        if (!$acc)
            throw $this->createAccessDeniedException();
        return $this->json($entityManager->getRepository(StoreroomTransferType::class)->findAll());
    }

    #[Route('/api/storeroom/ticket/insert', name: 'app_storeroom_ticket_insert')]
    public function app_storeroom_ticket_insert(PluginService $pluginService, registryMGR $registryMGR, SMS $sms, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('store');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        //check parameters exist
        if ((!array_key_exists('ticket', $params)) || (!array_key_exists('items', $params)) || (!array_key_exists('doc', $params)))
            $this->createNotFoundException();
        //going to save
        $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
            'id' => $params['doc']['id'],
            'bid' => $acc['bid'],
            'money' => $acc['money']
        ]);
        if (!$doc)
            throw $this->createNotFoundException('سند یافت نشد');
        if ($doc->getBid()->getId() != $acc['bid']->getId())
            throw $this->createAccessDeniedException('دسترسی به این سند را ندارید.');
        //find transfer type
        if (!array_key_exists('transferType', $params['ticket']))
            throw $this->createNotFoundException('نوع انتقال یافت نشد');
        $transferType = $entityManager->getRepository(StoreroomTransferType::class)->find($params['ticket']['transferType']['id']);
        if (!$transferType)
            throw $this->createNotFoundException('نوع انتقال یافت نشد');

        //find storeroom
        if (!array_key_exists('store', $params['ticket']))
            throw $this->createNotFoundException('انبار یافت نشد');
        $storeroom = $entityManager->getRepository(Storeroom::class)->find($params['ticket']['store']['id']);
        if (!$storeroom)
            throw $this->createNotFoundException('انبار یافت نشد');
        elseif ($storeroom->getBid()->getId() != $acc['bid']->getId())
            throw $this->createAccessDeniedException('دسترسی به این انبار ممکن نیست!');
        //find person
        if (!array_key_exists('person', $params['ticket']))
            throw $this->createNotFoundException('طرف حساب یافت نشد');
        $person = $entityManager->getRepository(Person::class)->find($params['ticket']['person']['id']);
        if (!$person)
            throw $this->createNotFoundException('طرف حساب یافت نشد');
        elseif ($person->getBid()->getId() != $acc['bid']->getId())
            throw $this->createAccessDeniedException('دسترسی به این طرف حساب ممکن نیست!');

        $ticket = new StoreroomTicket();
        $ticket->setSubmitter($this->getUser());
        $ticket->setDate($params['ticket']['date']);
        $ticket->setBid($acc['bid']);
        $ticket->setDateSubmit(time());
        $ticket->setDoc($doc);
        $ticket->setSenderTel($params['ticket']['senderTel']);
        $ticket->setTransfer($params['ticket']['transfer']);
        $ticket->setYear($acc['year']);
        $ticket->setCode($provider->getAccountingCode($acc['bid'], 'storeroom'));
        $ticket->setReceiver($params['ticket']['receiver']);
        $ticket->setTransferType($transferType);
        $ticket->setReferral($params['ticket']['referral']);
        $ticket->setStoreroom($storeroom);
        $ticket->setPerson($person);
        $ticket->setType($params['ticket']['type']);
        $ticket->setTypeString($params['ticket']['typeString']);
        $ticket->setDes($params['ticket']['des']);
        $entityManager->persist($ticket);
        //$entityManager->flush();

        //going to save rows
        $docRows = $entityManager->getRepository(HesabdariRow::class)->findBy([
            'doc' => $doc
        ]);
        foreach ($params['items'] as $item) {
            $row = $entityManager->getRepository(HesabdariRow::class)->findOneBy([
                'bid' => $acc['bid'],
                'doc' => $doc,
                'id' => $item['id'],
            ]);
            if (!$row)
                throw $this->createNotFoundException('کالا یافت نشد!');
            if (!$row->getCommodity())
                throw $this->createNotFoundException('کالا یافت نشد!');
            //check row count not upper ticket count
            if ($row->getCommdityCount() < $item['ticketCount'])
                throw $this->createNotFoundException('تعداد کالای اضافه شده بیشتر از تعداد کالا در فاکتور است.');
            $ticketItem = new StoreroomItem();
            $ticketItem->setBid($acc['bid']);
            $ticketItem->setStoreroom($storeroom);
            $ticketItem->setTicket($ticket);
            $ticketItem->setCount($item['ticketCount']);
            $ticketItem->setReferal($item['referral']);
            $ticketItem->setDes($item['des']);
            $ticketItem->setCommodity($row->getCommodity());
            $ticketItem->setType($item['type']);
            $entityManager->persist($ticketItem);
        }
        $entityManager->flush();
        //save logs
        $log->insert('انبارداری', 'حواله انبار با شماره ' . $ticket->getCode() . ' اضافه / ویرایش شد.', $this->getUser(), $acc['bid']);
        if ($pluginService->isActive('accpro', $acc['bid'])) {
            //notification to person
            if ($params['ticket']['sms'] == true) {
                $ticket->setCanShare(true);
                $entityManager->persist($ticket);
                $entityManager->flush();
                if ($transferType->getName() == 'باربری') {
                    if ($ticket->getTransfer() == null)
                        $ticket->setTransfer(0);
                    if ($ticket->getReferral() == null)
                        $ticket->setReferral(0);
                    if ($ticket->getSenderTel() == null)
                        $ticket->setSenderTel(0);
                    $smsres = $sms->sendByBalance(
                        [
                            $person->getNikename(),
                            $ticket->getTransfer(),
                            $ticket->getReferral(),
                            $ticket->getSenderTel(),
                            $acc['bid']->getName(),
                            $acc['bid']->getId() . '/' . $ticket->getId()
                        ],
                        $registryMGR->get('sms', 'plugAccproStoreroomSmsBarbari'),
                        $person->getMobile(),
                        $acc['bid'],
                        $this->getUser(),
                        3
                    );
                } else {
                    $smsres = $sms->sendByBalance(
                        [
                            $person->getNikename(),
                            $ticket->getReferral(),
                            $transferType->getName(),
                            $acc['bid']->getName(),
                            $acc['bid']->getId() . '/' . $ticket->getId()
                        ],
                        $registryMGR->get('sms', 'plugAccproStoreroomSmsOther'),
                        $person->getMobile(),
                        $acc['bid'],
                        $this->getUser(),
                        3
                    );
                }

                if ($smsres == 2) {
                    return $this->json([
                        'result' => 2
                    ]);
                }
            }
        }

        return $this->json([
            'result' => 0
        ]);
    }

    #[Route('/api/storeroom/tickets/list/{type}', name: 'app_storeroom_tickets_list')]
    public function app_storeroom_tickets_list(string $type, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('store');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $tickets = $entityManager->getRepository(StoreroomTicket::class)->findBy([
            'bid' => $acc['bid'],
            'year' => $acc['year'],
            'type' => $type
        ], [
            'date' => 'DESC'
        ]);
        return $this->json($provider->ArrayEntity2ArrayJustIncludes($tickets, [
            'getDes',
            'getCode',
            'getDate',
            'getPerson',
            'getNikename',
            'getDoc',
            'getTypeString'
        ]));
    }

    #[Route('/api/storeroom/tickets/info/{code}', name: 'app_storeroom_ticket_view')]
    public function app_storeroom_ticket_view(string $code, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('store');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $ticket = $entityManager->getRepository(StoreroomTicket::class)->findOneBy([
            'bid' => $acc['bid'],
            'code' => $code
        ]);
        if (!$ticket)
            throw $this->createNotFoundException('حواله یافت نشد.');
        //get items
        $items = $entityManager->getRepository(StoreroomItem::class)->findBy(['ticket' => $ticket]);
        $res = [];
        $res['ticket'] = $provider->Entity2ArrayJustIncludes($ticket, ['getStoreroom', 'getManager', 'getDate', 'getSubmitDate', 'getDes', 'getReceiver', 'getTransfer', 'getCode', 'getType', 'getReferral', 'getTypeString'], 2);
        $res['transferType'] = $provider->Entity2ArrayJustIncludes($ticket->getTransferType(), ['getName'], 0);
        $res['person'] = $provider->Entity2ArrayJustIncludes($ticket->getPerson(), ['getKeshvar', 'getOstan', 'getShahr', 'getAddress', 'getNikename', 'getCodeeghtesadi', 'getPostalcode', 'getName', 'getTel', 'getSabt'], 0);
        //get rows
        $rows = $entityManager->getRepository(StoreroomItem::class)->findBy(['ticket' => $ticket]);
        $res['commodities'] = $provider->ArrayEntity2ArrayJustIncludes($rows, ['getId', 'getDes', 'getCode', 'getName', 'getCommodity', 'getUnit', 'getCount', 'getReferal'], 2);

        //calculate rows data
        $this->calcStoreRemaining($res, $ticket->getDoc(), $entityManager);
        return $this->json($res);
    }
    #[Route('/api/storeroom/commodity/list/{sid}', name: 'app_storeroom_commodity_list')]
    public function app_storeroom_commodity_list(string $sid, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('store');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $store = $entityManager->getRepository(Storeroom::class)->find($sid);
        if (!$store)
            throw $this->createNotFoundException('انبار یافت نشد');
        if ($store->getBid() != $acc['bid'])
            throw $this->createAccessDeniedException('شما دسترسی به این انبار را ندارید.');

        $rows = $entityManager->getRepository(StoreroomItem::class)->findBy([
            'Storeroom' => $store
        ]);
        $commodities = $entityManager->getRepository(Commodity::class)->findBy([
            'bid' => $acc['bid'],
            'khadamat' => false
        ]);
        $items = [];
        foreach ($commodities as $commodity) {
            $temp = [];
            $temp['commodity'] = $provider->Entity2ArrayJustIncludes($commodity, ['getUnit', 'getCode', 'getName', 'getCat', 'getOrderPoint']);
            $temp['input'] = 0;
            $temp['output'] = 0;
            foreach ($rows as $row) {
                if ($row->getCommodity()->getId() == $commodity->getId()) {
                    if ($row->getType() == 'output')
                        $temp['output'] += $row->getCount();
                    elseif ($row->getType() == 'input')
                        $temp['input'] += $row->getCount();
                }
            }
            $temp['existCount'] = $temp['input'] - $temp['output'];
            $items[] = $temp;
        }
        return $this->json($this->getCommodityCountExistInStoreroom($commodities, $rows, $provider));
    }
    public function getCommodityCountExistInStoreroom(array $commodities, array $rows, Provider $provider)
    {
        $items = [];
        foreach ($commodities as $commodity) {
            $temp = [];
            $temp['commodity'] = $provider->Entity2ArrayJustIncludes($commodity, ['getUnit', 'getCode', 'getName', 'getCat', 'getOrderPoint']);
            $temp['input'] = 0;
            $temp['output'] = 0;
            foreach ($rows as $row) {
                if ($row->getCommodity()->getId() == $commodity->getId()) {
                    if ($row->getType() == 'output')
                        $temp['output'] += $row->getCount();
                    elseif ($row->getType() == 'input')
                        $temp['input'] += $row->getCount();
                }
            }
            $temp['existCount'] = $temp['input'] - $temp['output'];
            $items[] = $temp;
        }
        return $items;
    }
    #[Route('/api/storeroom/ticket/remove/{id}', name: 'app_storeroom_ticket_remove')]
    public function app_storeroom_ticket_remove(string $id, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('store');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $ticket = $entityManager->getRepository(StoreroomTicket::class)->findOneBy([
            'code' => $id,
            'bid' => $acc['bid']
        ]);
        if (!$ticket)
            throw $this->createNotFoundException('حواله یافت نشد');
        $items = $entityManager->getRepository(StoreroomItem::class)->findBy(['ticket' => $ticket]);
        foreach ($items as $item)
            $entityManager->remove($item);
        $entityManager->remove($ticket);
        $entityManager->flush();
        //save logs
        $log->insert('انبارداری', 'حواله انبار با شماره ' . $ticket->getCode() . ' حذف شد.', $this->getUser(), $acc['bid']);
        return $this->json([
            'result' => 0
        ]);
    }

    #[Route('/api/storeroom/print/ticket', name: 'app_storeroom_print_ticket')]
    public function app_storeroom_print_ticket(Printers $printers, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        $acc = $access->hasRole('store');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $doc = $entityManager->getRepository(StoreroomTicket::class)->findOneBy([
            'bid' => $acc['bid'],
            'code' => $params['code']
        ]);
        if (!$doc)
            throw $this->createNotFoundException();
        if ($params['type'] == 'input') {
            $title = 'حواله ورود به انبار';
        } else {
            $title = 'حواله خروج از انبار';
        }
        $pdfPid = 0;
        $pdfPid = $provider->createPrint(
            $acc['bid'],
            $this->getUser(),
            $this->renderView('pdf/printers/storeroom/input.html.twig', [
                'title' => $title,
                'bid' => $acc['bid'],
                'doc' => $doc,
                'rows' => $doc->getStoreroomItems(),
                'person' => $doc->getPerson()
            ]),
            false
        );
        return $this->json(['id' => $pdfPid]);
    }
}
