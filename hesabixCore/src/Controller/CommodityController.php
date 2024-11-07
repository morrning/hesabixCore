<?php

namespace App\Controller;

use App\Entity\PriceListDetail;
use App\Service\Explore;
use App\Service\Extractor;
use App\Service\Log;
use App\Service\Jdate;
use App\Service\Access;
use App\Entity\Business;
use App\Entity\Commodity;
use App\Entity\Storeroom;
use App\Service\Provider;
use App\Entity\CommodityCat;
use App\Entity\HesabdariRow;
use App\Entity\CommodityDrop;
use App\Entity\CommodityUnit;
use App\Entity\PriceList;
use App\Entity\StoreroomItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommodityController extends AbstractController
{
    #[Route('/api/commodity/search/extra', name: 'app_commodity_search_extra')]
    public function app_commodity_search_extra(Provider $provider, Extractor $extractor, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        $take = 10;
        if (array_key_exists('Take', $params['queryInfo']))
            $take = $params['queryInfo']['Take'];

        $items = $entityManager->getRepository(Commodity::class)->search([
            'bid' => $acc['bid'],
            'Take' => $take,
            'Filters' => $params['queryInfo']['Filters']
        ]);
        $res = [];
        foreach ($items as $item) {
            $res[] = Explore::ExploreCommodity($item);
        }
        return $this->json($extractor->operationSuccess([
            'List' => $res,
            'FilteredCount' => count($res)
        ]));
    }
    #[Route('/api/commodity/search/bycodes', name: 'app_commodity_search')]
    public function app_commodity_search(Provider $provider, Explore $explore, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        $items = $entityManager->getRepository(Commodity::class)->findBy([
            'bid' => $request->headers->get('activeBid'),
            'code' => $params['values']
        ]);
        $res = [];
        foreach ($items as $item) {
            $res[] = Explore::ExploreCommodity($item);
        }
        return $this->json([
            'Success' => true,
            'result' => $res
        ]);
    }

    #[Route('/api/commodity/list', name: 'app_commodity_list')]
    public function app_commodity_list(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (array_key_exists('speedAccess', $params)) {
            $items = $entityManager->getRepository(Commodity::class)->findBy([
                'bid' => $request->headers->get('activeBid'),
                'speedAccess' => true
            ]);
        } else {
            $items = $entityManager->getRepository(Commodity::class)->findBy([
                'bid' => $request->headers->get('activeBid')
            ]);
        }
        $res = [];
        foreach ($items as $item) {
            $temp = [];
            $temp['id'] = $item->getId();
            $temp['name'] = $item->getName();
            $temp['unit'] = $item->getUnit()->getName();
            $temp['des'] = $item->getDes();
            $temp['priceBuy'] = $item->getPriceBuy();
            $temp['speedAccess'] = $item->isSpeedAccess();
            $temp['priceSell'] = $item->getPriceSell();
            $temp['code'] = $item->getCode();
            $temp['cat'] = null;
            if ($item->getCat())
                $temp['cat'] = $item->getCat()->getName();
            $temp['khadamat'] = false;
            if ($item->isKhadamat())
                $temp['khadamat'] = true;
            $temp['withoutTax'] = false;
            if ($item->isWithoutTax())
                $temp['withoutTax'] = true;
            $temp['commodityCountCheck'] = $item->isCommodityCountCheck();
            $temp['minOrderCount'] = $item->getMinOrderCount();
            $temp['dayLoading'] = $item->getDayLoading();
            $temp['orderPoint'] = $item->getOrderPoint();
            $temp['unitData'] = [
                'name' => $item->getUnit()->getName(),
                'floatNumber' => $item->getUnit()->getFloatNumber(),
            ];
            //calculate count
            if ($item->isKhadamat()) {
                $temp['count'] = 0;
            } else {
                $rows = $entityManager->getRepository(HesabdariRow::class)->findBy([
                    'bid' => $acc['bid'],
                    'commodity' => $item
                ]);
                $count = 0;
                foreach ($rows as $row) {
                    if ($row->getDoc()->getType() == 'buy') {
                        $count += $row->getCommdityCount();
                    } elseif ($row->getDoc()->getType() == 'sell') {
                        $count -= $row->getCommdityCount();
                    }
                }
                $temp['count'] = $count;
            }
            $res[] = $temp;
        }
        return $this->json($res);
    }
    #[Route('/api/commodity/list/search', name: 'app_commodity_list_search')]
    public function app_commodity_list_search(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (array_key_exists('search', $params))
            $items = $entityManager->getRepository(Commodity::class)->searchByName($acc['bid'], $params['search'], 10);
        else
            $items = $entityManager->getRepository(Commodity::class)->getLasts($acc['bid'], 10);
        $res = [];
        foreach ($items as $key => $item) {
            $temp = [];
            $temp['id'] = $item->getId();
            $temp['name'] = $item->getName();
            $temp['unit'] = $item->getUnit()->getName();
            $temp['unitData'] = [
                'name' => $item->getUnit()->getName(),
                'floatNumber' => $item->getUnit()->getFloatNumber(),
            ];
            $temp['des'] = $item->getDes();
            $temp['priceBuy'] = $item->getPriceBuy();
            $temp['speedAccess'] = $item->isSpeedAccess();
            $temp['priceSell'] = $item->getPriceSell();
            $temp['code'] = $item->getCode();
            $temp['cat'] = null;
            if ($item->getCat())
                $temp['cat'] = $item->getCat()->getName();
            $temp['khadamat'] = false;
            if ($item->isKhadamat())
                $temp['khadamat'] = true;
            $temp['withoutTax'] = false;
            if ($item->isWithoutTax())
                $temp['withoutTax'] = true;
            $temp['commodityCountCheck'] = $item->isCommodityCountCheck();
            $temp['minOrderCount'] = $item->getMinOrderCount();
            $temp['dayLoading'] = $item->getDayLoading();
            $temp['orderPoint'] = $item->getOrderPoint();
            //calculate count
            if ($item->isKhadamat()) {
                $temp['count'] = 0;
            } else {
                $rows = $entityManager->getRepository(HesabdariRow::class)->findBy([
                    'bid' => $acc['bid'],
                    'commodity' => $item
                ]);
                $count = 0;
                foreach ($rows as $row) {
                    if ($row->getDoc()->getType() == 'buy') {
                        $count += $row->getCommdityCount();
                    } elseif ($row->getDoc()->getType() == 'sell') {
                        $count -= $row->getCommdityCount();
                    }
                }
                $temp['count'] = $count;
            }

            //calculate other prices
            $pricesAll = $entityManager->getRepository(PriceList::class)->findBy([
                'bid' => $acc['bid']
            ]);
            if (count($pricesAll) == 0) {
                $temp['prices'] = [];
            } else {
                foreach ($pricesAll as $list) {
                    $priceDetails = $entityManager->getRepository(PriceListDetail::class)->findOneBy([
                        'list' => $list,
                        'commodity' => $item
                    ]);
                    if ($priceDetails) {
                        $temp['prices'][] = Explore::ExploreCommodityPriceListDetail($priceDetails);
                    } else {
                        $spd = new PriceListDetail;
                        $spd->setList($list);
                        $spd->setMoney($acc['money']);
                        $spd->setCommodity($item);
                        $spd->setPriceBuy(0);
                        $spd->setPriceSell(0);
                        $entityManager->persist($spd);
                        $entityManager->flush();
                        $temp['prices'][] = Explore::ExploreCommodityPriceListDetail($spd);
                    }
                }
            }

            $res[] = $temp;
        }
        return $this->json($res);
    }
    /**
     * @throws Exception
     */
    #[Route('/api/commodity/list/excel', name: 'app_commodity_list_excel')]
    public function app_commodity_list_excel(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): BinaryFileResponse|JsonResponse|StreamedResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('items', $params)) {
            $items = $entityManager->getRepository(Commodity::class)->findBy([
                'bid' => $acc['bid']
            ]);
        } else {
            $items = [];
            foreach ($params['items'] as $param) {
                $prs = $entityManager->getRepository(Commodity::class)->findOneBy([
                    'id' => $param['id'],
                    'bid' => $acc['bid']
                ]);
                if ($prs)
                    $items[] = $prs;
            }
        }
        return new BinaryFileResponse($provider->createExcell($items));
    }

    #[Route('/api/commodity/list/print', name: 'app_commodity_list_print')]
    public function app_commodity_list_print(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('items', $params)) {
            $items = $entityManager->getRepository(Commodity::class)->findBy([
                'bid' => $request->headers->get('activeBid')
            ]);
        } else {
            $items = [];
            foreach ($params['items'] as $param) {
                $prs = $entityManager->getRepository(Commodity::class)->findOneBy([
                    'id' => $param['id'],
                    'bid' => $acc['bid']
                ]);
                if ($prs)
                    $items[] = $prs;
            }
        }

        $pid = $provider->createPrint(
            $acc['bid'],
            $this->getUser(),
            $this->renderView('pdf/commodity.html.twig', [
                'page_title' => 'فهرست کالا و خدمات',
                'bid' => $acc['bid'],
                'persons' => $items
            ])
        );
        return $this->json(['id' => $pid]);
    }
    #[Route('/api/commodity/info/{code}', name: 'app_commodity_info')]
    public function app_commodity_info($code, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $data = $entityManager->getRepository(Commodity::class)->findOneBy([
            'bid' => $acc['bid'],
            'code' => $code
        ]);
        $res = Explore::ExploreCommodity($data);
        $res['cat'] = '';
        if ($data->getCat())
            $res['cat'] = $data->getCat()->getId();
        $count = 0;
        //calculate count
        if ($data->isKhadamat()) {
            $res['count'] = 0;
        } else {
            $rows = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'bid' => $acc['bid'],
                'commodity' => $data
            ]);
            foreach ($rows as $row) {
                if ($row->getDoc()->getType() == 'buy') {
                    $count += $row->getCommdityCount();
                } elseif ($row->getDoc()->getType() == 'sell') {
                    $count -= $row->getCommdityCount();
                }
            }
            $res['count'] = $count;
        }

        //calculate other prices
        $pricesAll = $entityManager->getRepository(PriceList::class)->findBy([
            'bid' => $acc['bid']
        ]);
        if (count($pricesAll) == 0) {
            $res['prices'] = [];
        } else {
            foreach ($pricesAll as $item) {
                $priceDetails = $entityManager->getRepository(PriceListDetail::class)->findOneBy([
                    'list' => $item,
                    'commodity' => $data
                ]);
                if ($priceDetails) {
                    $res['prices'][] = Explore::ExploreCommodityPriceListDetail($priceDetails);
                } else {
                    $spd = new PriceListDetail;
                    $spd->setList($item);
                    $spd->setMoney($acc['money']);
                    $spd->setCommodity($data);
                    $spd->setPriceBuy(0);
                    $spd->setPriceSell(0);
                    $res['prices'][] = Explore::ExploreCommodityPriceListDetail($spd);
                }
            }
        }


        return $this->json($res);
    }

    #[Route('/api/commodity/group/mod', name: 'app_commodity_group_mod')]
    public function app_commodity_group_mod(Provider $provider, Extractor $extractor, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $paramsAll = [];
        if ($content = $request->getContent()) {
            $paramsAll = json_decode($content, true);
        }
        if (!array_key_exists('items', $paramsAll))
            return $this->json($extractor->paramsNotSend());
        foreach ($paramsAll['items'] as $params) {
            if (!array_key_exists('name', $params))
                return $this->json(['result' => -1]);
            if (count_chars(trim($params['name'])) == 0)
                return $this->json(['result' => 3]);
            if ($code == 0) {
                $data = $entityManager->getRepository(Commodity::class)->findOneBy([
                    'name' => $params['name'],
                    'bid' => $acc['bid']
                ]);
                //check exist before
                if (!$data) {
                    $data = new Commodity();
                    $data->setCode($provider->getAccountingCode($request->headers->get('activeBid'), 'Commodity'));
                }
            } else {
                $data = $entityManager->getRepository(Commodity::class)->findOneBy([
                    'bid' => $acc['bid'],
                    'code' => $code
                ]);
                if (!$data)
                    throw $this->createNotFoundException();
            }
            if (!array_key_exists('unit', $params))
                $unit = $entityManager->getRepository(CommodityUnit::class)->findAll()[0];
            else
                $unit = $entityManager->getRepository(CommodityUnit::class)->findOneBy(['name' => $params['unit']]);
            if (!$unit)
                throw $this->createNotFoundException('unit not fount!');
            $data->setUnit($unit);
            $data->setBid($acc['bid']);
            $data->setname($params['name']);
            if ($params['khadamat'] == 'true')
                $data->setKhadamat(true);
            else
                $data->setKhadamat(false);

            if (!array_key_exists('withoutTax', $params))
                $data->setWithoutTax(false);
            else {
                if ($params['withoutTax'] == 'true')
                    $data->setWithoutTax(true);
                else
                    $data->setWithoutTax(false);
            }

            if (array_key_exists('des', $params))
                $data->setDes($params['des']);

            if (array_key_exists('priceSell', $params))
                $data->setPriceSell($params['priceSell']);

            if (array_key_exists('priceBuy', $params))
                $data->setPriceBuy($params['priceBuy']);

            if (array_key_exists('commodityCountCheck', $params)) {
                $data->setCommodityCountCheck($params['commodityCountCheck']);
            }
            if (array_key_exists('barcodes', $params)) {
                $data->setBarcodes($params['barcodes']);
            }

            if (array_key_exists('minOrderCount', $params)) {
                $data->setMinOrderCount($params['minOrderCount']);
            }
            if (array_key_exists('speedAccess', $params)) {
                $data->setSpeedAccess($params['speedAccess']);
            }
            if (array_key_exists('dayLoading', $params)) {
                $data->setDayLoading($params['dayLoading']);
            }
            if (array_key_exists('orderPoint', $params)) {
                $data->setOrderPoint($params['orderPoint']);
            }
            //set cat
            if (array_key_exists('cat', $params)) {
                if ($params['cat'] != '') {
                    if (is_int($params['cat']))
                        $cat = $entityManager->getRepository(CommodityCat::class)->find($params['cat']);
                    else
                        $cat = $entityManager->getRepository(CommodityCat::class)->find($params['cat']['id']);
                    if ($cat) {
                        if ($cat->getBid() == $acc['bid']) {
                            $data->setCat($cat);
                        }
                    }
                }
            }
            $entityManager->persist($data);

            //save prices list
            if (array_key_exists('prices', $params)) {
                foreach ($params['prices'] as $item) {
                    $priceList = $entityManager->getRepository(PriceList::class)->findOneBy([
                        'bid' => $acc['bid'],
                        'id' => $item['list']['id']
                    ]);
                    if ($priceList) {
                        $detail = $entityManager->getRepository(PriceListDetail::class)->findOneBy([
                            'list' => $priceList,
                            'commodity' => $data
                        ]);
                        if (!$detail) {
                            $detail = new PriceListDetail;
                        }
                        $detail->setList($priceList);
                        $detail->setCommodity($data);
                        $detail->setPriceSell($item['priceSell']);
                        $detail->setPriceBuy(0);
                        $detail->setMoney($acc['money']);
                        $entityManager->persist($detail);
                    }
                }
            }
            $entityManager->flush();
            $log->insert('کالا و خدمات', 'کالا / خدمات با نام  ' . $params['name'] . ' افزوده/ویرایش شد.', $this->getUser(), $request->headers->get('activeBid'));
        }
        return $this->json([
            'Success' => true,
            'result' => 1,
        ]);

    }
    #[Route('/api/commodity/mod/{code}', name: 'app_commodity_mod')]
    public function app_commodity_mod(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('commodity');
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
            $data = $entityManager->getRepository(Commodity::class)->findOneBy([
                'name' => $params['name'],
                'bid' => $acc['bid']
            ]);
            //check exist before
            if (!$data) {
                $data = new Commodity();
                $data->setCode($provider->getAccountingCode($request->headers->get('activeBid'), 'Commodity'));
            }
        } else {
            $data = $entityManager->getRepository(Commodity::class)->findOneBy([
                'bid' => $acc['bid'],
                'code' => $code
            ]);
            if (!$data)
                throw $this->createNotFoundException();
        }
        if (!array_key_exists('unit', $params))
            $unit = $entityManager->getRepository(CommodityUnit::class)->findAll()[0];
        else
            $unit = $entityManager->getRepository(CommodityUnit::class)->findOneBy(['name' => $params['unit']]);
        if (!$unit)
            throw $this->createNotFoundException('unit not fount!');
        $data->setUnit($unit);
        $data->setBid($acc['bid']);
        $data->setname($params['name']);
        if ($params['khadamat'] == 'true')
            $data->setKhadamat(true);
        else
            $data->setKhadamat(false);

        if (!array_key_exists('withoutTax', $params))
            $data->setWithoutTax(false);
        else {
            if ($params['withoutTax'] == 'true')
                $data->setWithoutTax(true);
            else
                $data->setWithoutTax(false);
        }

        if (array_key_exists('des', $params))
            $data->setDes($params['des']);

        if (array_key_exists('priceSell', $params))
            $data->setPriceSell($params['priceSell']);

        if (array_key_exists('priceBuy', $params))
            $data->setPriceBuy($params['priceBuy']);

        if (array_key_exists('commodityCountCheck', $params)) {
            $data->setCommodityCountCheck($params['commodityCountCheck']);
        }
        if (array_key_exists('barcodes', $params)) {
            $data->setBarcodes($params['barcodes']);
        }

        if (array_key_exists('minOrderCount', $params)) {
            $data->setMinOrderCount($params['minOrderCount']);
        }
        if (array_key_exists('speedAccess', $params)) {
            $data->setSpeedAccess($params['speedAccess']);
        }
        if (array_key_exists('dayLoading', $params)) {
            $data->setDayLoading($params['dayLoading']);
        }
        if (array_key_exists('orderPoint', $params)) {
            $data->setOrderPoint($params['orderPoint']);
        }
        //set cat
        if (array_key_exists('cat', $params)) {
            if ($params['cat'] != '') {
                if (is_int($params['cat']))
                    $cat = $entityManager->getRepository(CommodityCat::class)->find($params['cat']);
                else
                    $cat = $entityManager->getRepository(CommodityCat::class)->find($params['cat']['id']);
                if ($cat) {
                    if ($cat->getBid() == $acc['bid']) {
                        $data->setCat($cat);
                    }
                }
            }
        }
        $entityManager->persist($data);

        //save prices list
        if (array_key_exists('prices', $params)) {
            foreach ($params['prices'] as $item) {
                $priceList = $entityManager->getRepository(PriceList::class)->findOneBy([
                    'bid' => $acc['bid'],
                    'id' => $item['list']['id']
                ]);
                if ($priceList) {
                    $detail = $entityManager->getRepository(PriceListDetail::class)->findOneBy([
                        'list' => $priceList,
                        'commodity' => $data
                    ]);
                    if (!$detail) {
                        $detail = new PriceListDetail;
                    }
                    $detail->setList($priceList);
                    $detail->setCommodity($data);
                    $detail->setPriceSell($item['priceSell']);
                    $detail->setPriceBuy(0);
                    $detail->setMoney($acc['money']);
                    $entityManager->persist($detail);
                }
            }
        }
        $entityManager->flush();
        $log->insert('کالا و خدمات', 'کالا / خدمات با نام  ' . $params['name'] . ' افزوده/ویرایش شد.', $this->getUser(), $request->headers->get('activeBid'));
        return $this->json([
            'Success' => true,
            'result' => 1,
            'code' => $data->getId()
        ]);
    }

    #[Route('/api/commodity/units', name: 'app_commodity_units')]
    public function app_commodity_units(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$access->hasRole('commodity'))
            throw $this->createAccessDeniedException();
        $items = $entityManager->getRepository(CommodityUnit::class)->findAll();
        return $this->json($items);
    }

    #[Route('/api/commodity/drop/list', name: 'app_commodity_drop_list')]
    public function app_commodity_drop_list(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$access->hasRole('commodity'))
            throw $this->createAccessDeniedException();
        $items = $entityManager->getRepository(CommodityDrop::class)->findBy([
            'bid' => $request->headers->get('activeBid')
        ]);
        $generalItems = $entityManager->getRepository(CommodityDrop::class)->findBy([
            'bid' => null
        ]);

        return $this->json($provider->ArrayEntity2Array(array_merge($items, $generalItems), 0));
    }

    #[Route('/api/commodity/drop/mod/{code}', name: 'app_commodity_drop_mod')]
    public function app_commodity_drop_mod(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('commodity');
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
            $data = $entityManager->getRepository(CommodityDrop::class)->findOneBy([
                'name' => $params['name'],
                'bid' => $acc['bid']
            ]);
            //check exist before
            if ($data)
                return $this->json(['result' => 2]);
            $data = new CommodityDrop();
        } else {
            $data = $entityManager->getRepository(CommodityDrop::class)->findOneBy([
                'bid' => $acc['bid'],
                'id' => $code
            ]);
            if (!$data)
                throw $this->createNotFoundException();
        }
        $data->setName($params['name']);
        $data->setBid($acc['bid']);
        $data->setCanEdit(true);
        $entityManager->persist($data);
        $entityManager->flush();
        $log->insert('کالا و خدمات', 'ویژگی کالا / خدمات با نام ' . $params['name'] . ' افزوده/ویرایش شد.', $this->getUser(), $request->headers->get('activeBid'));
        return $this->json(['result' => 1]);
    }

    /**
     * @throws \ReflectionException
     */
    #[Route('/api/commodity/drop/info/{code}', name: 'app_commodity_drop_info')]
    public function app_commodity_drop_info($code, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $data = $entityManager->getRepository(CommodityDrop::class)->findOneBy([
            'bid' => $acc['bid'],
            'id' => $code
        ]);
        return $this->json($provider->Entity2Array($data, 0));
    }
    #[Route('/api/commodity/cat/get/line', name: 'app_commodity_cat_get_line')]
    public function app_commodity_cat_get_line(Jdate $jdate, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $temp = [];
        $nodes = $entityManager->getRepository(CommodityCat::class)->findBy([
            'bid' => $acc['bid'],
        ]);
        if (count($nodes) == 0)
            $nodes = $this->createDefaultCat($acc['bid'], $entityManager);
        return $this->json($provider->ArrayEntity2Array($nodes, 0));
    }

    #[Route('/api/commodity/cat/get', name: 'app_commodity_cat_get')]
    public function app_commodity_cat_get(Jdate $jdate, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {

        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $temp = [];
        $nodes = $entityManager->getRepository(CommodityCat::class)->findBy([
            'bid' => $acc['bid']
        ]);
        if (count($nodes) == 0)
            $nodes = $this->createDefaultCat($acc['bid'], $entityManager);
        foreach ($nodes as $node) {
            if ($this->hasChild($entityManager, $node)) {
                $temp[$node->getId()] = [
                    'text' => $node->getName(),
                    'children' => $this->getChildsLabel($entityManager, $node)
                ];
            } else {
                $temp[$node->getId()] = [
                    'text' => $node->getName(),
                ];
            }
        }
        $root = $entityManager->getRepository(CommodityCat::class)->findOneBy([
            'bid' => $acc['bid'],
            'root' => true
        ]);
        return $this->json(['items' => $temp, 'root' => $root->getId()]);
    }

    #[Route('/api/commodity/cat/childs', name: 'app_commodity_cat_childs')]
    public function app_commodity_cat_childs(Jdate $jdate, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $items = $entityManager->getRepository(CommodityCat::class)->findOneBy([
            'bid' => $acc['bid'],
            'root' => true
        ]);
        return $this->json($this->getChilds($entityManager, $items));
    }
    #[Route('/api/commodity/cat/insert', name: 'app_commodity_cat_insert')]
    public function app_commodity_cat_insert(Jdate $jdate, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('upper', $params) || !array_key_exists('text', $params))
            return $this->json(['result' => -1]);
        $upper = $entityManager->getRepository(CommodityCat::class)->find($params['upper']);
        if ($upper) {
            if ($upper->getBid() == $acc['bid']) {
                $cat = new CommodityCat();
                $cat->setBid($acc['bid']);
                $cat->setRoot(false);
                $cat->setName($params['text']);
                $cat->setUpper($upper->getId());
                $entityManager->persist($cat);
                $entityManager->flush();
                return $this->json(['result' => 1, 'id' => $cat->getId()]);
            }
        }
        return $this->json(['result' => 1]);
    }
    #[Route('/api/commodity/cat/edit', name: 'app_commodity_cat_edit')]
    public function app_commodity_cat_edit(Jdate $jdate, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('id', $params) || !array_key_exists('text', $params))
            return $this->json(['result' => -1]);
        $node = $entityManager->getRepository(CommodityCat::class)->find($params['id']);
        if ($node) {
            if ($node->getBid() == $acc['bid']) {
                $node->setName($params['text']);
                $entityManager->persist($node);
                $entityManager->flush();
                return $this->json(['result' => 1, 'id' => $node->getId()]);
            }
        }
        return $this->json(['result' => 1]);
    }
    private function getChildsLabel(EntityManagerInterface $entityManager, mixed $node)
    {
        $childs = $entityManager->getRepository(CommodityCat::class)->findBy([
            'upper' => $node
        ]);
        $temp = [];
        foreach ($childs as $child) {
            $temp[] = $child->getId();
        }
        return $temp;
    }

    private function hasChild(EntityManagerInterface $entityManager, mixed $node)
    {
        if (
            count($entityManager->getRepository(CommodityCat::class)->findBy([
                'upper' => $node
            ])) != 0
        )
            return true;
        return false;
    }

    private function getChilds(EntityManagerInterface $entityManager, mixed $node)
    {
        $childs = $entityManager->getRepository(CommodityCat::class)->findBy([
            'upper' => $node
        ]);
        $temp = [];
        foreach ($childs as $child) {
            if ($this->hasChild($entityManager, $child)) {
                $temp[] = [
                    'id' => $child->getId(),
                    'label' => $child->getName(),
                    'children' => $this->getChilds($entityManager, $child)
                ];
            } else {
                $temp[] = [
                    'id' => $child->getId(),
                    'label' => $child->getName(),
                ];
            }
        }
        return $temp;
    }

    public function createDefaultCat(Business $bid, EntityManagerInterface $en): array
    {
        $item = new CommodityCat();
        $item->setName('دسته بندی ها');
        $item->setUpper(null);
        $item->setBid($bid);
        $item->setRoot(true);
        $en->persist($item);
        $en->flush();

        $child = new CommodityCat();
        $child->setUpper($item->getId());
        $child->setBid($bid);
        $child->setName('بدون دسته‌بندی');
        $en->persist($child);
        $en->flush();
        return [$item, $child];
    }

    #[Route('/api/commodity/import/excel', name: 'app_commodity_import_excel')]
    public function app_commodity_import_excel(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $file = $request->files->get('file');
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);
        $sheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());
        $data = $sheet->toArray();
        unset($data[0]);

        foreach ($data as $item) {
            //load cat
            $unit = $entityManager->getRepository(commodity::class)->findOneBy([
                'name' => $item[7],
            ]);
            if (!$unit) {
                $unit = $entityManager->getRepository(CommodityUnit::class)->findAll()[0];
            }

            $commodity = $entityManager->getRepository(commodity::class)->findOneBy([
                'name' => $item[2],
                'bid' => $acc['bid']
            ]);
            $cat = $entityManager->getRepository(CommodityCat::class)->findOneBy([
                'name' => $item[8],
                'bid' => $acc['bid']
            ]);

            $rootcat = $entityManager->getRepository(CommodityCat::class)->findOneBy([
                'name' => 'دسته بندی ها',
                'bid' => $acc['bid'],
                'root' => '1',
                'upper' => null
            ]);
            if (!$cat) {
                $cat = new CommodityCat();
                $cat->setBid($acc['bid']);
                $cat->setName($item[8]);
                $cat->setUpper($rootcat->getId());
                $cat->setRoot(1);
                $entityManager->persist($cat);
                $entityManager->flush();
            }
            //check exist before
            if (!$commodity) {
                $commodity = new commodity();
                $commodity->setCode($provider->getAccountingCode($request->headers->get('activeBid'), 'commodity'));
                $commodity->setBid($acc['bid']);
            }
            $commodity->setName($item[2]);
            $commodity->setUnit($unit);
            $commodity->setCat($cat);
            $commodity->setOrderPoint(0);
            $commodity->setDayLoading(0);
            if (array_key_exists(3, $item))
                $commodity->setPriceSell($item[3]);
            if (array_key_exists(4, $item))
                $commodity->setPriceBuy($item[4]);
            if (array_key_exists(1, $item))
                $commodity->setSpeedAccess($item[1]);
            if (array_key_exists(5, $item))
                $commodity->setMinOrderCount($item[5]);
            if (array_key_exists(6, $item))
                $commodity->setDes($item[6]);
            if (array_key_exists(0, $item)) {
                $commodity->setKhadamat(true);
                if ($item[0] == '1') {
                    $commodity->setKhadamat(false);
                }
            }
            $entityManager->persist($commodity);
            $entityManager->flush();
        }
        $log->insert('کالا/خدمات', 'تعداد ' . count($data) . ' کالا یا خدمات به صورت گروهی وارد شد.', $this->getUser(), $request->headers->get('activeBid'));
        return $this->json(['result' => 1]);
    }

    #[Route('/api/commodity/delete/{code}', name: 'app_commodity_delete')]
    public function app_commodity_delete(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $commodity = $entityManager->getRepository(Commodity::class)->findOneBy(['bid' => $acc['bid'], 'code' => $code]);
        if (!$commodity)
            throw $this->createNotFoundException();
        //check accounting docs
        $docs = $entityManager->getRepository(HesabdariRow::class)->findby(['bid' => $acc['bid'], 'commodity' => $commodity]);
        if (count($docs) > 0)
            return $this->json(['result' => 2]);
        //check for storeroom docs
        $storeDocs = $entityManager->getRepository(StoreroomItem::class)->findby(['bid' => $acc['bid'], 'commodity' => $commodity]);
        if (count($storeDocs) > 0)
            return $this->json(['result' => 2]);

        $comName = $commodity->getName();
        $entityManager->remove($commodity);
        $log->insert('کالا/خدمات', ' کالا / خدمات با نام ' . $comName . ' حذف شد. ', $this->getUser(), $acc['bid']->getId());
        return $this->json(['result' => 1]);
    }

    #[Route('/api/commodity/pricelist/list', name: 'app_commodity_pricelist_list')]
    public function app_commodity_pricelist_list(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $items = $entityManager->getRepository(PriceList::class)->findBy([
            'bid' => $acc['bid']
        ]);
        return $this->json(Explore::ExploreCommodityPriceList($items));
    }

    #[Route('/api/commodity/pricelist/view/{id}', name: 'app_commodity_pricelist_view')]
    public function app_commodity_pricelist_view($id, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $price = $entityManager->getRepository(PriceList::class)->findOneBy([
            'bid' => $acc['bid'],
            'id' => $id
        ]);
        $items = $entityManager->getRepository(PriceListDetail::class)->findBy([
            'list' => $price
        ]);
        $res = [];
        foreach ($items as $item) {
            $temp = [];
            $temp['id'] = $item->getId();
            $temp['commodity'] = Explore::ExploreCommodity($item->getCommodity());
            $temp['priceSell'] = $item->getPriceSell();
            $res[] = $temp;
        }
        return $this->json($res);
    }
    #[Route('/api/commodity/pricelist/mod/{code}', name: 'app_commodity_pricelist_mod')]
    public function app_commodity_pricelist_mod(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('label', $params))
            return $this->json(['result' => -1]);
        if (count_chars(trim($params['label'])) == 0)
            return $this->json(['result' => 3]);
        if ($code == 0) {
            $data = $entityManager->getRepository(PriceList::class)->findOneBy([
                'label' => $params['label'],
                'bid' => $acc['bid']
            ]);
            //check exist before
            if ($data)
                return $this->json(['result' => 2]);
            $data = new PriceList();
        } else {
            $data = $entityManager->getRepository(PriceList::class)->findOneBy([
                'bid' => $acc['bid'],
                'id' => $code
            ]);
            if (!$data)
                throw $this->createNotFoundException();
        }
        $data->setLabel($params['label']);
        $data->setBid($acc['bid']);
        $entityManager->persist($data);
        $entityManager->flush();
        $log->insert('کالا و خدمات', 'فهرست قیمت  کالا / خدمات با نام ' . $params['label'] . ' افزوده/ویرایش شد.', $this->getUser(), $request->headers->get('activeBid'));
        return $this->json(['result' => 1]);
    }

    /**
     * @throws \ReflectionException
     */
    #[Route('/api/commodity/pricelist/info/{code}', name: 'app_commodity_pricelist_info')]
    public function app_commodity_pricelist_info($code, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $data = $entityManager->getRepository(PriceList::class)->findOneBy([
            'bid' => $acc['bid'],
            'id' => $code
        ]);
        return $this->json($provider->Entity2Array($data, 0));
    }

    #[Route('/api/commodity/pricelist/delete/{code}', name: 'app_commodity_pricelist_delete')]
    public function app_commodity_pricelist_delete(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $item = $entityManager->getRepository(PriceList::class)->findOneBy(['bid' => $acc['bid'], 'id' => $code]);
        if (!$item)
            throw $this->createNotFoundException();

        $comName = $item->getLabel();
        $entityManager->remove($item);
        $log->insert('کالا/خدمات', 'فهرست قیمت کالا و خدمات با نام ' . $comName . ' حذف شد. ', $this->getUser(), $acc['bid']->getId());
        return $this->json(['result' => 1]);
    }
}
