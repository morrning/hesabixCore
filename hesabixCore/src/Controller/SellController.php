<?php

namespace App\Controller;

use App\Service\AccountingPermissionService;
use App\Service\Jdate;
use App\Service\Log;
use App\Service\Access;
use App\Service\Explore;
use App\Entity\Commodity;
use App\Service\PluginService;
use App\Service\Provider;
use App\Service\Extractor;
use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Entity\HesabdariTable;
use App\Entity\InvoiceType;
use App\Entity\Person;
use App\Entity\PrintOptions;
use App\Entity\StoreroomTicket;
use App\Service\Printers;
use App\Service\registryMGR;
use App\Service\SMS;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SellController extends AbstractController
{
    #[Route('/api/sell/edit/can/{code}', name: 'app_sell_can_edit')]
    public function app_sell_can_edit(Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, string $code): JsonResponse
    {
        $canEdit = true;
        $acc = $access->hasRole('sell');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
            'bid' => $acc['bid'],
            'code' => $code,
            'money' => $acc['money']
        ]);
        if (count($doc->getRelatedDocs()) != 0)
            $canEdit = false;

        $tickets = $entityManager->getRepository(StoreroomTicket::class)->findBy(['doc' => $doc]);
        if (count($tickets) != 0)
            $canEdit = false;
        return $this->json([
            'result' => $canEdit
        ]);
    }

    #[Route('/api/sell/get/info/{code}', name: 'app_sell_get_info')]
    public function app_sell_get_info(Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, string $code): JsonResponse
    {
        $acc = $access->hasRole('sell');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
            'bid' => $acc['bid'],
            'code' => $code,
            'money' => $acc['money']
        ]);
        if (!$doc)
            throw $this->createNotFoundException();
        $result = Explore::ExploreSellDoc($doc);
        $profit = 0;
        foreach ($doc->getHesabdariRows() as $item) {
            if ($item->getCommodity() && $item->getCommdityCount()) {
                if ($acc['bid']->getProfitCalctype() == 'simple') {
                    $profit = $profit + (($item->getCommodity()->getPriceSell() - $item->getCommodity()->getPriceSell()) * $item->getCommdityCount());
                } elseif ($acc['bid']->getProfitCalctype() == 'lis') {
                    $last = $entityManager->getRepository(HesabdariRow::class)->findOneBy([
                        'commodity' => $item->getCommodity(),
                        'bs' => 0
                    ], [
                        'id' => 'DESC'
                    ]);
                    if ($last) {
                        $price = $last->getBd() / $last->getCommdityCount();
                        $profit = $profit + ((($item->getBs() / $item->getCommdityCount()) - $price) * $item->getCommdityCount());
                    } else {
                        $profit = $profit + $item->getBs();
                    }
                } else {
                    $lasts = $entityManager->getRepository(HesabdariRow::class)->findBy([
                        'commodity' => $item->getCommodity(),
                        'bs' => 0
                    ], [
                        'id' => 'DESC'
                    ]);
                    $avg = 0;
                    $count = 0;
                    foreach ($lasts as $last) {
                        $avg = $avg + $last->getBd();
                        $count = $count + $last->getCommdityCount();
                    }
                    if ($count != 0) {
                        $price = $avg / $count;
                        $profit = $profit + ((($item->getBs() / $item->getCommdityCount()) - $price) * $item->getCommdityCount());
                    } else {
                        $profit = $profit + $item->getBs();
                    }
                }
                $profit = round($profit);
            }
        }
        $result['profit'] = $profit;
        return $this->json($result);
    }

    #[Route('/api/sell/mod', name: 'app_sell_mod')]
    public function app_sell_mod(AccountingPermissionService $accountingPermissionService, PluginService $pluginService, SMS $SMS, Provider $provider, Extractor $extractor, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        $acc = $access->hasRole('sell');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $pkgcntr = $accountingPermissionService->canRegisterAccountingDoc($acc['bid']);
        if ($pkgcntr['code'] == 4) {
            return $this->json([
                'result' => 4,
                'message' => $pkgcntr['message']
            ]);
        }
        if (!array_key_exists('update', $params)) {
            return $this->json($extractor->paramsNotSend());
        }
        if ($params['update'] != '') {
            $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                'bid' => $acc['bid'],
                'year' => $acc['year'],
                'code' => $params['update'],
                'money' => $acc['money']
            ]);
            if (!$doc)
                return $this->json($extractor->notFound());

            $rows = $doc->getHesabdariRows();
            foreach ($rows as $row)
                $entityManager->remove($row);
        } else {
            $doc = new HesabdariDoc();
            $doc->setBid($acc['bid']);
            $doc->setYear($acc['year']);
            $doc->setDateSubmit(time());
            $doc->setType('sell');
            $doc->setSubmitter($this->getUser());
            $doc->setMoney($acc['money']);
            $doc->setCode($provider->getAccountingCode($acc['bid'], 'accounting'));
        }
        if ($params['transferCost'] != 0) {
            $hesabdariRow = new HesabdariRow();
            $hesabdariRow->setDes('حمل و نقل کالا');
            $hesabdariRow->setBid($acc['bid']);
            $hesabdariRow->setYear($acc['year']);
            $hesabdariRow->setDoc($doc);
            $hesabdariRow->setBs($params['transferCost']);
            $hesabdariRow->setBd(0);
            $ref = $entityManager->getRepository(HesabdariTable::class)->findOneBy([
                'code' => '61'
            ]);
            $hesabdariRow->setRef($ref);
            $entityManager->persist($hesabdariRow);
        }
        if ($params['discountAll'] != 0) {
            $hesabdariRow = new HesabdariRow();
            $hesabdariRow->setDes('تخفیف فاکتور');
            $hesabdariRow->setBid($acc['bid']);
            $hesabdariRow->setYear($acc['year']);
            $hesabdariRow->setDoc($doc);
            $hesabdariRow->setBs(0);
            $hesabdariRow->setBd($params['discountAll']);
            $ref = $entityManager->getRepository(HesabdariTable::class)->findOneBy([
                'code' => '104'
            ]);
            $hesabdariRow->setRef($ref);
            $entityManager->persist($hesabdariRow);
        }
        $doc->setDes($params['des']);
        $doc->setDate($params['date']);
        $sumTax = 0;
        $sumTotal = 0;
        foreach ($params['rows'] as $row) {
            $sumTax += $row['tax'];
            $sumTotal += $row['sumWithoutTax'];
            $hesabdariRow = new HesabdariRow();
            $hesabdariRow->setDes($row['des']);
            $hesabdariRow->setBid($acc['bid']);
            $hesabdariRow->setYear($acc['year']);
            $hesabdariRow->setDoc($doc);
            $hesabdariRow->setBs($row['sumWithoutTax'] + $row['tax']);
            $hesabdariRow->setBd(0);
            $hesabdariRow->setDiscount($row['discount']);
            $hesabdariRow->setTax($row['tax']);
            $ref = $entityManager->getRepository(HesabdariTable::class)->findOneBy([
                'code' => '53'
            ]);
            $hesabdariRow->setRef($ref);
            $row['count'] = str_replace(',', '', $row['count']);
            $commodity = $entityManager->getRepository(Commodity::class)->findOneBy([
                'id' => $row['commodity']['id'],
                'bid' => $acc['bid']
            ]);
            if (!$commodity)
                return $this->json($extractor->paramsNotSend());
            $hesabdariRow->setCommodity($commodity);
            $hesabdariRow->setCommdityCount($row['count']);
            $entityManager->persist($hesabdariRow);

            if ($acc['bid']->isCommodityUpdateSellPriceAuto() == true && $commodity->getPriceSell() != $row['price']) {
                $commodity->setPriceSell($row['price']);
                $entityManager->persist($commodity);
            }
        }
        $doc->setAmount($sumTax + $sumTotal - $params['discountAll'] + $params['transferCost']);
        $hesabdariRow = new HesabdariRow();
        $hesabdariRow->setDes('فاکتور فروش');
        $hesabdariRow->setBid($acc['bid']);
        $hesabdariRow->setYear($acc['year']);
        $hesabdariRow->setDoc($doc);
        $hesabdariRow->setBs(0);
        $hesabdariRow->setBd($sumTax + $sumTotal + $params['transferCost'] - $params['discountAll']);
        $ref = $entityManager->getRepository(HesabdariTable::class)->findOneBy([
            'code' => '3'
        ]);
        $hesabdariRow->setRef($ref);
        $person = $entityManager->getRepository(Person::class)->findOneBy([
            'bid' => $acc['bid'],
            'code' => $params['person']['code']
        ]);
        if (!$person)
            return $this->json($extractor->paramsNotSend());
        $hesabdariRow->setPerson($person);
        $entityManager->persist($hesabdariRow);

        $entityManager->persist($doc);
        $entityManager->flush();
        if (!$doc->getShortlink()) {
            $doc->setShortlink($provider->RandomString(8));
        }

        if (array_key_exists('pair_docs', $params)) {
            foreach ($params['pair_docs'] as $pairCode) {
                $pair = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                    'bid' => $acc['bid'],
                    'code' => $pairCode,
                    'type' => 'buy'
                ]);
                if ($pair) {
                    $doc->addPairDoc($pair);
                }
            }
        }
        $entityManager->persist($doc);
        $entityManager->flush();

        $log->insert(
            'حسابداری',
            'سند حسابداری شماره ' . $doc->getCode() . ' ثبت / ویرایش شد.',
            $this->getUser(),
            $request->headers->get('activeBid'),
            $doc
        );
        if (array_key_exists('sms', $params)) {
            if ($params['sms'] == true) {
                if ($pluginService->isActive('accpro', $acc['bid']) && $person->getMobile() != '' && $acc['bid']->getTel()) {
                    return $this->json([
                        'result' =>
                            $SMS->sendByBalance(
                                [$person->getnikename(), 'sell/' . $acc['bid']->getId() . '/' . $doc->getId(), $acc['bid']->getName(), $acc['bid']->getTel()],
                                $registryMGR->get('sms', 'plugAccproSharefaktor'),
                                $person->getMobile(),
                                $acc['bid'],
                                $this->getUser(),
                                3
                            )
                    ]);
                } else {
                    return $this->json([
                        'result' =>
                            $SMS->sendByBalance(
                                [$acc['bid']->getName(), 'sell/' . $acc['bid']->getId() . '/' . $doc->getId()],
                                $registryMGR->get('sms', 'sharefaktor'),
                                $person->getMobile(),
                                $acc['bid'],
                                $this->getUser(),
                                3
                            )
                    ]);
                }
            }
        }
        return $this->json($extractor->operationSuccess());
    }

    #[Route('/api/sell/label/change', name: 'app_sell_label_change')]
    public function app_sell_label_change(Request $request, Access $access, Extractor $extractor, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        $acc = $access->hasRole('sell');
        if (!$acc)
            throw $this->createAccessDeniedException();
        if ($params['label'] != 'clear') {
            $label = $entityManager->getRepository(InvoiceType::class)->findOneBy([
                'code' => $params['label']['code'],
                'type' => 'sell'
            ]);
            if (!$label)
                return $this->json($extractor->notFound());
        }
        foreach ($params['items'] as $item) {
            $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                'bid' => $acc['bid'],
                'year' => $acc['year'],
                'code' => $item['code'],
                'money' => $acc['money']
            ]);
            if (!$doc)
                return $this->json($extractor->notFound());
            if ($params['label'] != 'clear') {
                $doc->setInvoiceLabel($label);
                $entityManager->persist($doc);
                $log->insert(
                    'حسابداری',
                    ' تغییر برچسب فاکتور‌ شماره ' . $doc->getCode() . ' به ' . $label->getLabel(),
                    $this->getUser(),
                    $acc['bid']->getId(),
                    $doc
                );
            } else {
                $doc->setInvoiceLabel(null);
                $entityManager->persist($doc);
                $log->insert(
                    'حسابداری',
                    ' حذف برچسب فاکتور‌ شماره ' . $doc->getCode(),
                    $this->getUser(),
                    $acc['bid']->getId(),
                    $doc
                );
            }
        }
        $entityManager->flush();
        return $this->json($extractor->operationSuccess());
    }

    #[Route('/api/sell/docs/search', name: 'app_sell_docs_search', methods: ['POST'])]
public function searchSellDocs(
    Provider $provider,
    Request $request,
    Access $access,
    Log $log,
    EntityManagerInterface $entityManager,
    Jdate $jdate
): JsonResponse {
    $acc = $access->hasRole('sell');
    if (!$acc) {
        throw $this->createAccessDeniedException();
    }

    $params = json_decode($request->getContent(), true) ?? [];
    $searchTerm = $params['search'] ?? '';
    $page = max(1, $params['page'] ?? 1);
    $perPage = max(1, min(100, $params['perPage'] ?? 10));
    $types = $params['types'] ?? [];
    $dateFilter = $params['dateFilter'] ?? 'all';
    $sortBy = $params['sortBy'] ?? [];

    $queryBuilder = $entityManager->createQueryBuilder()
        ->select('DISTINCT d.id, d.dateSubmit, d.date, d.type, d.code, d.des, d.amount')
        ->addSelect('u.fullName as submitter')
        ->addSelect('l.code as labelCode, l.label as labelLabel')
        ->from(HesabdariDoc::class, 'd')
        ->leftJoin('d.submitter', 'u')
        ->leftJoin('d.InvoiceLabel', 'l')
        ->leftJoin('d.hesabdariRows', 'r')
        ->where('d.bid = :bid')
        ->andWhere('d.year = :year')
        ->andWhere('d.type = :type')
        ->andWhere('d.money = :money')
        ->setParameter('bid', $acc['bid'])
        ->setParameter('year', $acc['year'])
        ->setParameter('type', 'sell')
        ->setParameter('money', $acc['money']);

    // اعمال فیلترهای تاریخ
    $today = $jdate->jdate('Y/m/d', time());
    if ($dateFilter === 'today') {
        $queryBuilder->andWhere('d.date = :today')
            ->setParameter('today', $today);
    } elseif ($dateFilter === 'week') {
        $weekStart = $jdate->jdate('Y/m/d', strtotime('-6 days'));
        $queryBuilder->andWhere('d.date BETWEEN :weekStart AND :today')
            ->setParameter('weekStart', $weekStart)
            ->setParameter('today', $today);
    } elseif ($dateFilter === 'month') {
        $monthStart = $jdate->jdate('Y/m/01', time());
        $queryBuilder->andWhere('d.date BETWEEN :monthStart AND :today')
            ->setParameter('monthStart', $monthStart)
            ->setParameter('today', $today);
    }

    if ($searchTerm) {
        $queryBuilder->leftJoin('r.person', 'p')
            ->andWhere(
                $queryBuilder->expr()->orX(
                    'd.code LIKE :search',
                    'd.des LIKE :search',
                    'd.date LIKE :search',
                    'd.amount LIKE :search',
                    'p.nikename LIKE :search',
                    'p.mobile LIKE :search'
                )
            )
            ->setParameter('search', "%$searchTerm%");
    }

    if (!empty($types)) {
        $queryBuilder->andWhere('l.code IN (:types)')
            ->setParameter('types', $types);
    }

    // فیلدهای معتبر برای مرتب‌سازی توی دیتابیس
    $validDbFields = [
        'id' => 'd.id',
        'dateSubmit' => 'd.dateSubmit',
        'date' => 'd.date',
        'type' => 'd.type',
        'code' => 'd.code',
        'des' => 'd.des',
        'amount' => 'd.amount',
        'mdate' => 'd.mdate',
        'plugin' => 'd.plugin',
        'refData' => 'd.refData',
        'shortlink' => 'd.shortlink',
        'status' => 'd.status',
        'submitter' => 'u.fullName',
        'label' => 'l.label', // از InvoiceLabel
    ];

    // اعمال مرتب‌سازی توی دیتابیس
    if (!empty($sortBy)) {
        foreach ($sortBy as $sort) {
            $key = $sort['key'] ?? 'id';
            $direction = isset($sort['order']) && strtoupper($sort['order']) === 'DESC' ? 'DESC' : 'ASC';
            if ($key === 'profit' || $key === 'receivedAmount') {
                continue; // این‌ها توی PHP مرتب می‌شن
            } elseif (isset($validDbFields[$key])) {
                $queryBuilder->addOrderBy($validDbFields[$key], $direction);
            }
            // اگه کلید معتبر نبود، نادیده گرفته می‌شه
        }
    } else {
        $queryBuilder->orderBy('d.id', 'DESC');
    }

    $totalItemsQuery = clone $queryBuilder;
    $totalItems = $totalItemsQuery->select('COUNT(DISTINCT d.id)')
        ->getQuery()
        ->getSingleScalarResult();

    $queryBuilder->setFirstResult(($page - 1) * $perPage)
        ->setMaxResults($perPage);

    $docs = $queryBuilder->getQuery()->getArrayResult();

    $dataTemp = [];
    foreach ($docs as $doc) {
        $item = [
            'id' => $doc['id'],
            'dateSubmit' => $doc['dateSubmit'],
            'date' => $doc['date'],
            'type' => $doc['type'],
            'code' => $doc['code'],
            'des' => $doc['des'],
            'amount' => $doc['amount'],
            'submitter' => $doc['submitter'],
            'label' => $doc['labelCode'] ? [
                'code' => $doc['labelCode'],
                'label' => $doc['labelLabel']
            ] : null,
        ];

        $mainRow = $entityManager->getRepository(HesabdariRow::class)
            ->createQueryBuilder('r')
            ->where('r.doc = :docId')
            ->andWhere('r.person IS NOT NULL')
            ->setParameter('docId', $doc['id'])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        $item['person'] = $mainRow && $mainRow->getPerson() ? [
            'id' => $mainRow->getPerson()->getId(),
            'nikename' => $mainRow->getPerson()->getNikename(),
            'code' => $mainRow->getPerson()->getCode()
        ] : null;

        // استفاده از SQL خام برای محاسبه پرداختی‌ها
        $sql = "
            SELECT SUM(rd.amount) as total_pays, COUNT(rd.id) as count_docs
            FROM hesabdari_doc rd
            JOIN hesabdari_doc_hesabdari_doc rel ON rel.hesabdari_doc_target = rd.id
            WHERE rel.hesabdari_doc_source = :sourceDocId
            AND rd.bid_id = :bidId
        ";
        $stmt = $entityManager->getConnection()->prepare($sql);
        $stmt->bindValue('sourceDocId', $doc['id']);
        $stmt->bindValue('bidId', $acc['bid']->getId());
        $result = $stmt->executeQuery()->fetchAssociative();

        $relatedDocsPays = $result['total_pays'] ?? 0;
        $relatedDocsCount = $result['count_docs'] ?? 0;

        $item['relatedDocsCount'] = (int) $relatedDocsCount;
        $item['relatedDocsPays'] = $relatedDocsPays;
        $item['profit'] = $this->calculateProfit($doc['id'], $acc, $entityManager);
        $item['discountAll'] = 0;
        $item['transferCost'] = 0;

        $rows = $entityManager->getRepository(HesabdariRow::class)->findBy(['doc' => $doc]);
        foreach ($rows as $row) {
            if ($row->getRef()->getCode() == '104') {
                $item['discountAll'] = $row->getBd();
            } elseif ($row->getRef()->getCode() == '61') {
                $item['transferCost'] = $row->getBs();
            }
        }

        $dataTemp[] = $item;
    }

    // مرتب‌سازی توی PHP برای profit و receivedAmount
    if (!empty($sortBy)) {
        foreach ($sortBy as $sort) {
            $key = $sort['key'] ?? 'id';
            $direction = isset($sort['order']) && strtoupper($sort['order']) === 'DESC' ? SORT_DESC : SORT_ASC;
            if ($key === 'profit') {
                usort($dataTemp, function ($a, $b) use ($direction) {
                    return $direction === SORT_ASC ? $a['profit'] - $b['profit'] : $b['profit'] - $a['profit'];
                });
            } elseif ($key === 'receivedAmount') {
                usort($dataTemp, function ($a, $b) use ($direction) {
                    return $direction === SORT_ASC ? $a['relatedDocsPays'] - $b['relatedDocsPays'] : $b['relatedDocsPays'] - $a['relatedDocsPays'];
                });
            }
        }
    }

    return $this->json([
        'items' => $dataTemp,
        'total' => (int) $totalItems,
        'page' => $page,
        'perPage' => $perPage,
    ]);
}

// متد calculateProfit بدون تغییر
private function calculateProfit(int $docId, array $acc, EntityManagerInterface $entityManager): int
{
    $profit = 0;
    $rows = $entityManager->getRepository(HesabdariRow::class)->findBy(['doc' => $docId]);
    foreach ($rows as $item) {
        if ($item->getCommdityCount() && $item->getBs()) {
            $commodityId = $item->getCommodity() ? $item->getCommodity()->getId() : null;
            if ($acc['bid']->getProfitCalctype() === 'lis') {
                if ($commodityId) {
                    $last = $entityManager->getRepository(HesabdariRow::class)
                        ->findOneBy(['commodity' => $commodityId, 'bs' => 0], ['id' => 'DESC']);
                    if ($last) {
                        $price = $last->getBd() / $last->getCommdityCount();
                        $profit += ((($item->getBs() / $item->getCommdityCount()) - $price) * $item->getCommdityCount());
                    } else {
                        $profit += $item->getBs();
                    }
                } else {
                    $profit += $item->getBs();
                }
            } elseif ($acc['bid']->getProfitCalctype() === 'simple') {
                if ($item->getCommodity() && $item->getCommodity()->getPriceSell() !== null && $item->getCommodity()->getPriceBuy() !== null) {
                    $profit += (($item->getCommodity()->getPriceSell() - $item->getCommodity()->getPriceBuy()) * $item->getCommdityCount());
                } else {
                    $profit += $item->getBs();
                }
            } else {
                if ($commodityId) {
                    $lasts = $entityManager->getRepository(HesabdariRow::class)
                        ->findBy(['commodity' => $commodityId, 'bs' => 0], ['id' => 'DESC']);
                    $avg = array_sum(array_map(fn($last) => $last->getBd(), $lasts));
                    $count = array_sum(array_map(fn($last) => $last->getCommdityCount(), $lasts));
                    if ($count != 0) {
                        $price = $avg / $count;
                        $profit += ((($item->getBs() / $item->getCommdityCount()) - $price) * $item->getCommdityCount());
                    } else {
                        $profit += $item->getBs();
                    }
                } else {
                    $profit += $item->getBs();
                }
            }
        }
    }
    return round($profit);
}

    #[Route('/api/sell/rows/{code}', name: 'app_sell_rows', methods: ['GET'])]
    public function getSellRows(
        Request $request,
        Access $access,
        EntityManagerInterface $entityManager,
        string $code,
        Log $log // سرویس Log را اضافه کنید
    ): JsonResponse {
        $acc = $access->hasRole('sell');
        if (!$acc) {
            $log->insert('SellController', 'Access denied for code: ' . $code, $this->getUser(), null);
            throw $this->createAccessDeniedException();
        }

        $log->insert('SellController', 'Searching for doc with code: ' . $code, $this->getUser(), $acc['bid']->getId());
        $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
            'bid' => $acc['bid'],
            'code' => $code,
            'money' => $acc['money'],
        ]);

        if (!$doc) {
            $log->insert('SellController', 'Doc not found for code: ' . $code, $this->getUser(), $acc['bid']->getId());
            throw $this->createNotFoundException();
        }

        $rows = $entityManager->getRepository(HesabdariRow::class)->findBy(['doc' => $doc]);
        $log->insert('SellController', 'Found ' . count($rows) . ' rows for code: ' . $code, $this->getUser(), $acc['bid']->getId());

        $data = array_map(function ($row) use ($log) {
            try {
                return [
                    'id' => $row->getId(),
                    'des' => $row->getDes(),
                    'bs' => $row->getBs(),
                    'commdityCount' => $row->getCommdityCount(),
                    'commodity' => $row->getCommodity() ? [
                        'id' => $row->getCommodity()->getId(),
                        'name' => $row->getCommodity()->getName(),
                    ] : null,
                ];
            } catch (\Exception $e) {
                $log->insert('SellController', 'Error processing row: ' . $e->getMessage(), $this->getUser(), null);
                return null;
            }
        }, $rows);

        // فیلتر کردن موارد null
        $data = array_filter($data);

        return $this->json(['rows' => array_values($data)]);
    }

    #[Route('/api/sell/print/invoice', name: 'app_sell_print_invoice')]
    public function app_sell_print_invoice(Printers $printers, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        $acc = $access->hasRole('sell');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
            'bid' => $acc['bid'],
            'code' => $params['code'],
            'money' => $acc['money']
        ]);
        if (!$doc)
            throw $this->createNotFoundException();
        $person = null;
        $discount = 0;
        $transfer = 0;
        foreach ($doc->getHesabdariRows() as $item) {
            if ($item->getPerson()) {
                $person = $item->getPerson();
            } elseif ($item->getRef()->getCode() == 104) {
                $discount = $item->getBd();
            } elseif ($item->getRef()->getCode() == 61) {
                $transfer = $item->getBs();
            }
        }
        $pdfPid = 0;
        if ($params['pdf']) {
            $printOptions = [
                'bidInfo' => true,
                'pays' => true,
                'taxInfo' => true,
                'discountInfo' => true,
                'note' => true,
                'paper' => 'A4-L'
            ];
            if (array_key_exists('printOptions', $params)) {
                if (array_key_exists('bidInfo', $params['printOptions'])) {
                    $printOptions['bidInfo'] = $params['printOptions']['bidInfo'];
                }
                if (array_key_exists('pays', $params['printOptions'])) {
                    $printOptions['pays'] = $params['printOptions']['pays'];
                }
                if (array_key_exists('taxInfo', $params['printOptions'])) {
                    $printOptions['taxInfo'] = $params['printOptions']['taxInfo'];
                }
                if (array_key_exists('discountInfo', $params['printOptions'])) {
                    $printOptions['discountInfo'] = $params['printOptions']['discountInfo'];
                }
                if (array_key_exists('note', $params['printOptions'])) {
                    $printOptions['note'] = $params['printOptions']['note'];
                }
                if (array_key_exists('paper', $params['printOptions'])) {
                    $printOptions['paper'] = $params['printOptions']['paper'];
                }
            }
            $note = '';
            $printSettings = $entityManager->getRepository(PrintOptions::class)->findOneBy(['bid' => $acc['bid']]);
            if ($printSettings) {
                $note = $printSettings->getSellNoteString();
            }
            $pdfPid = $provider->createPrint(
                $acc['bid'],
                $this->getUser(),
                $this->renderView('pdf/printers/sell.html.twig', [
                    'bid' => $acc['bid'],
                    'doc' => $doc,
                    'rows' => $doc->getHesabdariRows(),
                    'person' => $person,
                    'printInvoice' => $params['printers'],
                    'discount' => $discount,
                    'transfer' => $transfer,
                    'printOptions' => $printOptions,
                    'note' => $note
                ]),
                false,
                $printOptions['paper']
            );
        }
        if ($params['printers'] == true) {
            $pid = $provider->createPrint(
                $acc['bid'],
                $this->getUser(),
                $this->renderView('pdf/posPrinters/justSell.html.twig', [
                    'bid' => $acc['bid'],
                    'doc' => $doc,
                    'rows' => $doc->getHesabdariRows(),
                ]),
                false
            );
            $printers->addFile($pid, $acc, "fastSellInvoice");
        }
        return $this->json(['id' => $pdfPid]);
    }

    #[Route('/api/sell/chart/data', name: 'app_sell_chart_data')]
    public function app_sell_chart_data(Jdate $jdate, Printers $printers, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {

        $acc = $access->hasRole('sell');
        if (!$acc)
            throw $this->createAccessDeniedException();
        // create data numbers
        $dayTime = 3600 * 24;
        $dayNames = [];
        $daySells = [];
        for ($i = 0; $i < 7; $i++) {
            $dayInfo = [
                $jdate->jdate('l', time() - ($i * $dayTime)),
                $jdate->jdate('Y/n/d', time() - ($i * $dayTime))
            ];
            $dayNames[] = $jdate->jdate('l', time() - ($i * $dayTime));
            //get sell docs
            $docs = $entityManager->getRepository(HesabdariDoc::class)->findBy([
                'bid' => $acc['bid'],
                'money' => $acc['money'],
                'year' => $acc['year'],
                'type' => 'sell',
                'date' => $dayInfo[1],
            ]);
            $bd = 0;
            foreach ($docs as $doc) {
                foreach ($doc->getHesabdariRows() as $row) {
                    if ($row->getPerson()) {
                        $bd += $row->getBd();
                    }
                }
            }
            $daySells[] = $bd;
        }
        return $this->json([
            'dayNames' => $dayNames,
            'daySells' => $daySells
        ]);
    }
}