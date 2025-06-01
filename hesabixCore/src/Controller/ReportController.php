<?php

namespace App\Controller;

use OpenApi\Annotations as OA;
use App\Entity\BankAccount;
use App\Entity\Cashdesk;
use App\Entity\Commodity;
use App\Entity\HesabdariTable;
use App\Entity\Person;
use App\Entity\Salary;
use App\Service\Access;
use App\Service\pdfMGR;
use App\Service\Provider;
use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Service\Explore;
use App\Service\Jdate;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\PrintOptions;

class ReportController extends AbstractController
{
    private $em;
    private $provider;

    function __construct(Provider $provider, EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->provider = $provider;
    }

    #[Route('/api/report/person/buysell', name: 'app_report_person_buysell')]
    public function app_report_person_buysell(Provider $provider, Jdate $jdate, Access $access, Request $request, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $acc = $access->hasRole('report');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if ($params['type'] == 'all') {
            $docs = $entityManagerInterface->getRepository(HesabdariDoc::class)->findBy([
                'year' => $acc['year'],
                'bid' => $acc['bid'],
                'money' => $acc['money']
            ]);
        } else {
            $docs = $entityManagerInterface->getRepository(HesabdariDoc::class)->findBy([
                'year' => $acc['year'],
                'bid' => $acc['bid'],
                'type' => $params['type'],
                'money' => $acc['money']
            ]);
        }
        //filter docs by date
        $result = [];
        $dateStart = $provider->shamsiDateToTimestamp($params['dateStart']);
        $dateEnd = $provider->shamsiDateToTimestamp($params['dateEnd']);
        foreach ($docs as $doc) {
            $canAdd = true;
            if ($dateStart) {
                if ($provider->shamsiDateToTimestamp($doc->getDate()) < $dateStart)
                    $canAdd = false;
            }
            if ($dateEnd) {
                if ($provider->shamsiDateToTimestamp($doc->getDate()) > $dateEnd)
                    $canAdd = false;
            }

            if ($canAdd)
                $result[] = $doc;
        }
        $docs = $result;

        $person = $entityManagerInterface->getRepository(Person::class)->findOneBy([
            'bid' => $acc['bid']->getId(),
            'code' => $params['person'],
        ]);
        $result = [];
        foreach ($docs as $doc) {
            $rows = $doc->getHesabdariRows();
            foreach ($rows as $row) {
                if ($row->getPerson()) {
                    if ($person->getId() == $row->getPerson()->getId()) {
                        $result[] = $doc;
                    }
                }
            }
        }
        $docs = $result;
        $result = [];
        foreach ($docs as $doc) {
            $rows = $doc->getHesabdariRows();
            foreach ($rows as $row) {
                if ($row->getCommodity()) {
                    $result[] = $row;
                }
            }
        }
        $response = [];
        foreach ($result as $item) {
            $temp = [
                'id' => $item->getCommodity()->getId(),
                'rowId' => $item->getId(),
                'code' => $item->getCommodity()->getCode(),
                'khadamat' => $item->getCommodity()->isKhadamat(),
                'name' => $item->getCommodity()->getName(),
                'unit' => $item->getCommodity()->getUnit()->getName(),
                'count' => $item->getCommdityCount(),
                'date' => $item->getDoc()->getDate(),
                'docCode' => $item->getDoc()->getCode(),
                'type' => $item->getDoc()->getType()
            ];
            if ($item->getDoc()->getType() == 'buy' || $item->getDoc()->getType() == 'rfsell') {
                $temp['priceAll'] = $item->getBd();
            } elseif ($item->getDoc()->getType() == 'sell' || $item->getDoc()->getType() == 'rfbuy') {
                $temp['priceAll'] = $item->getBs();
            }

            if ($temp['count'] != 0) {
                $temp['priceOne'] = $temp['priceAll'] / $temp['count'];
                $temp['priceAll'] = number_format($temp['priceAll']);
                $temp['priceOne'] = number_format($temp['priceOne']);
                $temp['count'] = number_format($temp['count']);
                $response[] = $temp;
            }
        }
        return $this->json($response);
    }

    #[Route('/api/report/person/buysell/export/excel', name: 'app_report_person_buysell_export_excell')]
    public function app_report_person_buysell_export_excell(Provider $provider, Access $access, Request $request, EntityManagerInterface $entityManagerInterface): BinaryFileResponse|JsonResponse|StreamedResponse
    {
        $acc = $access->hasRole('report');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        $items = [];
        foreach ($params['items'] as $param) {
            $prs = $entityManagerInterface->getRepository(HesabdariRow::class)->findOneBy([
                'id' => $param['rowId'],
                'bid' => $acc['bid']
            ]);
            if ($prs)
                $items[] = $prs;
        }

        $response = [];
        foreach ($items as $item) {
            $temp = [
                'id' => $item->getCommodity()->getId(),
                'code' => $item->getCommodity()->getCode(),
                'khadamat' => $item->getCommodity()->isKhadamat(),
                'name' => $item->getCommodity()->getName(),
                'unit' => $item->getCommodity()->getUnit()->getName(),
                'count' => $item->getCommdityCount(),
                'date' => $item->getDoc()->getDate(),
                'docCode' => $item->getDoc()->getCode(),
                'type' => $item->getDoc()->getType()
            ];
            if ($item->getDoc()->getType() == 'buy') {
                $temp['priceAll'] = $item->getBd();
            } elseif ($item->getDoc()->getType() == 'sell') {
                $temp['priceAll'] = $item->getBs();
            }
            if ($temp['count'] != 0) {
                $temp['priceOne'] = $temp['priceAll'] / $temp['count'];
                $temp['priceAll'] = number_format($temp['priceAll']);
                $temp['priceOne'] = number_format($temp['priceOne']);
                $temp['count'] = number_format($temp['count']);
                $response[] = $temp;
            }
        }
        $labels = [
            'ردیف',
            'کد کالا',
            'نام کالا',
            'واحد',
            'تعداد',
            'قیمت واحد',
            'قیمت کل',
            'تاریخ',
            'شماره سند',
            'نوع سند',
            'خدمات'
        ];
        
        // اضافه کردن شماره ردیف به داده‌ها
        $responseWithRow = [];
        foreach ($response as $index => $item) {
            $responseWithRow[] = [
                'row' => $index + 1,
                'code' => $item['code'],
                'name' => $item['name'],
                'unit' => $item['unit'],
                'count' => $item['count'],
                'priceOne' => $item['priceOne'],
                'priceAll' => $item['priceAll'],
                'date' => $item['date'],
                'docCode' => $item['docCode'],
                'type' => $item['type'],
                'khadamat' => $item['khadamat']
            ];
        }
        
        return new BinaryFileResponse($provider->createExcellFromArray($responseWithRow, $labels));
    }

    #[Route('/api/report/commodity/buysell', name: 'app_report_commodity_buysell')]
    public function app_report_commodity_buysell(Provider $provider, Jdate $jdate, Access $access, Request $request, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $acc = $access->hasRole('report');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if ($params['type'] == 'all') {
            $docs = $entityManagerInterface->getRepository(HesabdariDoc::class)->findBy([
                'year' => $acc['year'],
                'bid' => $acc['bid'],
                'money' => $acc['money']
            ]);
        } else {
            $docs = $entityManagerInterface->getRepository(HesabdariDoc::class)->findBy([
                'year' => $acc['year'],
                'bid' => $acc['bid'],
                'type' => $params['type'],
                'money' => $acc['money']
            ]);
        }

        $commodity = $entityManagerInterface->getRepository(Commodity::class)->findOneBy([
            'bid' => $acc['bid']->getId(),
            'code' => $params['commodity'],
        ]);
        //filter docs by date
        $result = [];
        $dateStart = $provider->shamsiDateToTimestamp($params['dateStart']);
        $dateEnd = $provider->shamsiDateToTimestamp($params['dateEnd']);
        foreach ($docs as $doc) {
            $canAdd = true;
            if ($dateStart) {
                if ($provider->shamsiDateToTimestamp($doc->getDate()) < $dateStart)
                    $canAdd = false;
            }
            if ($dateEnd) {
                if ($provider->shamsiDateToTimestamp($doc->getDate()) > $dateEnd)
                    $canAdd = false;
            }

            if ($canAdd)
                $result[] = $doc;
        }
        $docs = $result;
        $result = [];
        foreach ($docs as $doc) {
            $rows = $doc->getHesabdariRows();
            foreach ($rows as $row) {
                if ($row->getCommodity())
                    if ($row->getCommodity()->getId() == $commodity->getId()) {
                        $result[] = $row;
                    }
            }
        }

        $response = [];
        foreach ($result as $item) {
            $temp = [
                'id' => $item->getCommodity()->getId(),
                'rowId' => $item->getId(),
                'code' => $item->getCommodity()->getCode(),
                'khadamat' => $item->getCommodity()->isKhadamat(),
                'name' => $item->getCommodity()->getName(),
                'unit' => $item->getCommodity()->getUnit()->getName(),
                'count' => $item->getCommdityCount(),
                'date' => $item->getDoc()->getDate(),
                'docCode' => $item->getDoc()->getCode(),
                'type' => $item->getDoc()->getType(),
            ];
            if ($item->getDoc()->getType() == 'buy' || $item->getDoc()->getType() == 'rfsell') {
                $temp['priceAll'] = $item->getBd();
            } elseif ($item->getDoc()->getType() == 'sell' || $item->getDoc()->getType() == 'rfbuy') {
                $temp['priceAll'] = $item->getBs();
            }
            if ($temp['count'] != 0) {
                $temp['priceOne'] = $temp['priceAll'] / $temp['count'];
                $temp['priceAll'] = number_format($temp['priceAll']);
                $temp['priceOne'] = number_format($temp['priceOne']);
                $temp['count'] = number_format($temp['count']);
            }
            //find person
            foreach ($item->getDoc()->getHesabdariRows() as $rw) {
                if ($rw->getPerson()) {
                    $temp['person'] = Explore::ExplorePerson($rw->getPerson());
                }
            }
            $response[] = $temp;
        }
        return $this->json($response);
    }

    #[Route('/api/report/top-selling-commodities', name: 'app_report_top_selling_commodities', methods: ['POST'])]
    public function app_report_top_selling_commodities(Access $access, Explore $explore, Jdate $jdate, Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger): JsonResponse
    {
        $acc = $access->hasRole('report');
        if (!$acc) {
            $acc = $access->hasRole('sell');
            if (!$acc) {
                throw $this->createAccessDeniedException('شما دسترسی لازم برای مشاهده این اطلاعات را ندارید.');
            }
        }

        /** @var Business $business */
        $business = $acc['bid'];
        /** @var Year $year */
        $year = $acc['year'];

        $payload = $request->getPayload();
        $period = $payload->get('period', 'year');
        $limit = (int) $payload->get('limit', 10);
        if ($limit < 3) {
            $limit = 3;
        }

        $today = $jdate->GetTodayDate();
        list($currentYear, $currentMonth, $currentDay) = explode('/', $today);

        switch ($period) {
            case 'today':
                $dateStart = $today;
                $dateEnd = $today;
                break;
            case 'week':
                $weekDay = (int) $jdate->jdate('w', time());
                $daysToSubtract = $weekDay;
                $dateStart = $jdate->shamsiDate(0, 0, -$daysToSubtract);
                $dateEnd = $jdate->shamsiDate(0, 0, 6 - $weekDay);
                break;
            case 'month':
                $dateStart = "$currentYear/$currentMonth/01";
                $dateEnd = "$currentYear/$currentMonth/" . $jdate->jdate('t', $jdate->jallaliToUnixTime("$currentYear/$currentMonth/01"));
                break;
            case 'year':
            default:
                $dateStart = $jdate->jdate('Y/m/d', $year->getStart());
                $dateEnd = $jdate->jdate('Y/m/d', $year->getEnd());
                break;
        }

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('c.id AS id')
            ->addSelect('c.code AS code')
            ->addSelect('c.name AS name')
            ->addSelect('c.des AS des')
            ->addSelect('c.priceBuy AS priceBuy')
            ->addSelect('c.priceSell AS priceSell')
            ->addSelect('c.khadamat AS khadamat')
            ->addSelect('c.orderPoint AS orderPoint')
            ->addSelect('c.commodityCountCheck AS commodityCountCheck')
            ->addSelect('c.minOrderCount AS minOrderCount')
            ->addSelect('c.dayLoading AS dayLoading')
            ->addSelect('c.speedAccess AS speedAccess')
            ->addSelect('c.withoutTax AS withoutTax')
            ->addSelect('c.barcodes AS barcodes')
            ->addSelect('IDENTITY(c.unit) AS unitId') // ID واحد شمارش
            ->addSelect('u.name AS unit') // نام واحد شمارش
            ->addSelect('SUM(CAST(hr.commdityCount AS INTEGER)) AS totalCount') // مجموع فروش
            ->from(HesabdariRow::class, 'hr')
            ->innerJoin('hr.doc', 'hd')
            ->innerJoin('hr.commodity', 'c')
            ->leftJoin('c.unit', 'u') // برای گرفتن نام واحد شمارش
            ->where('hd.bid = :business')
            ->andWhere('hd.type = :type')
            ->andWhere('hr.year = :year')
            ->andWhere('hd.date BETWEEN :dateStart AND :dateEnd')
            ->setParameter('business', $business)
            ->setParameter('type', 'sell')
            ->setParameter('year', $year)
            ->setParameter('dateStart', $dateStart)
            ->setParameter('dateEnd', $dateEnd)
            ->groupBy('c.id') // گروه‌بندی فقط بر اساس شناسه کالا
            ->addGroupBy('u.name') // برای اطمینان از گروه‌بندی درست با واحد
            ->orderBy('totalCount', 'DESC')
            ->setMaxResults($limit);

        try {
            $results = $queryBuilder->getQuery()->getArrayResult(); // نتیجه به صورت آرایه
            $logger->info('Query executed successfully', [
                'sql' => $queryBuilder->getQuery()->getSQL(),
                'params' => $queryBuilder->getQuery()->getParameters()->toArray(),
                'results' => $results
            ]);

            if (empty($results)) {
                $logger->info('No results returned from query');
                return $this->json(['message' => 'No data found'], 200);
            }

            // فرمت‌بندی خروجی
            $topCommodities = array_map(function ($result) {
                return [
                    'id' => $result['id'],
                    'code' => $result['code'],
                    'name' => $result['name'],
                    'des' => $result['des'],
                    'priceBuy' => $result['priceBuy'],
                    'priceSell' => $result['priceSell'],
                    'khadamat' => $result['khadamat'],
                    'orderPoint' => $result['orderPoint'],
                    'commodityCountCheck' => $result['commodityCountCheck'],
                    'minOrderCount' => $result['minOrderCount'],
                    'dayLoading' => $result['dayLoading'],
                    'speedAccess' => $result['speedAccess'],
                    'withoutTax' => $result['withoutTax'],
                    'barcodes' => $result['barcodes'],
                    'unit' => $result['unit'] ?? '', // نام واحد شمارش
                    'count' => (int) $result['totalCount'] // مجموع فروش
                ];
            }, $results);

            return $this->json($topCommodities);
        } catch (\Exception $e) {
            $logger->error('Error in top-selling commodities query', [
                'message' => $e->getMessage(),
                'sql' => $queryBuilder->getQuery()->getSQL(),
                'params' => $queryBuilder->getQuery()->getParameters()->toArray(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/report/top-selling-commodities-by-price', name: 'app_report_top_selling_commodities_by_price', methods: ['POST'])]
    public function app_report_top_selling_commodities_by_price(Access $access, Explore $explore, Jdate $jdate, Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger): JsonResponse
    {
        $acc = $access->hasRole('report');
        if (!$acc) {
            $acc = $access->hasRole('sell');
            if (!$acc) {
                throw $this->createAccessDeniedException('شما دسترسی لازم برای مشاهده این اطلاعات را ندارید.');
            }
        }

        /** @var Business $business */
        $business = $acc['bid'];
        /** @var Year $year */
        $year = $acc['year'];

        $payload = $request->getPayload();
        $period = $payload->get('period', 'year');
        $limit = (int) $payload->get('limit', 10);
        if ($limit < 3) {
            $limit = 3;
        }

        $today = $jdate->GetTodayDate();
        list($currentYear, $currentMonth, $currentDay) = explode('/', $today);

        switch ($period) {
            case 'today':
                $dateStart = $today;
                $dateEnd = $today;
                break;
            case 'week':
                $weekDay = (int) $jdate->jdate('w', time());
                $daysToSubtract = $weekDay;
                $dateStart = $jdate->shamsiDate(0, 0, -$daysToSubtract);
                $dateEnd = $jdate->shamsiDate(0, 0, 6 - $weekDay);
                break;
            case 'month':
                $dateStart = "$currentYear/$currentMonth/01";
                $dateEnd = "$currentYear/$currentMonth/" . $jdate->jdate('t', $jdate->jallaliToUnixTime("$currentYear/$currentMonth/01"));
                break;
            case 'year':
            default:
                $dateStart = $jdate->jdate('Y/m/d', $year->getStart());
                $dateEnd = $jdate->jdate('Y/m/d', $year->getEnd());
                break;
        }

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('c.id AS id')
            ->addSelect('c.code AS code')
            ->addSelect('c.name AS name')
            ->addSelect('c.des AS des')
            ->addSelect('c.priceBuy AS priceBuy')
            ->addSelect('c.priceSell AS priceSell')
            ->addSelect('c.khadamat AS khadamat')
            ->addSelect('c.orderPoint AS orderPoint')
            ->addSelect('c.commodityCountCheck AS commodityCountCheck')
            ->addSelect('c.minOrderCount AS minOrderCount')
            ->addSelect('c.dayLoading AS dayLoading')
            ->addSelect('c.speedAccess AS speedAccess')
            ->addSelect('c.withoutTax AS withoutTax')
            ->addSelect('c.barcodes AS barcodes')
            ->addSelect('IDENTITY(c.unit) AS unitId')
            ->addSelect('u.name AS unit')
            ->addSelect('SUM(CAST(hr.commdityCount AS INTEGER)) AS totalCount')
            ->addSelect('SUM(hr.bs) AS totalPrice') // محاسبه مجموع قیمت فروش با استفاده از فیلد bs
            ->from(HesabdariRow::class, 'hr')
            ->innerJoin('hr.doc', 'hd')
            ->innerJoin('hr.commodity', 'c')
            ->leftJoin('c.unit', 'u')
            ->where('hd.bid = :business')
            ->andWhere('hd.type = :type')
            ->andWhere('hr.year = :year')
            ->andWhere('hd.date BETWEEN :dateStart AND :dateEnd')
            ->setParameter('business', $business)
            ->setParameter('type', 'sell')
            ->setParameter('year', $year)
            ->setParameter('dateStart', $dateStart)
            ->setParameter('dateEnd', $dateEnd)
            ->groupBy('c.id')
            ->addGroupBy('u.name')
            ->orderBy('totalPrice', 'DESC') // مرتب‌سازی بر اساس مجموع قیمت فروش
            ->setMaxResults($limit);

        try {
            $results = $queryBuilder->getQuery()->getArrayResult();
            $logger->info('Query executed successfully', [
                'sql' => $queryBuilder->getQuery()->getSQL(),
                'params' => $queryBuilder->getQuery()->getParameters()->toArray(),
                'results' => $results
            ]);

            if (empty($results)) {
                $logger->info('No results returned from query');
                return $this->json(['message' => 'No data found'], 200);
            }

            $topCommodities = array_map(function ($result) {
                return [
                    'id' => $result['id'],
                    'code' => $result['code'],
                    'name' => $result['name'],
                    'des' => $result['des'],
                    'priceBuy' => $result['priceBuy'],
                    'priceSell' => $result['priceSell'],
                    'khadamat' => $result['khadamat'],
                    'orderPoint' => $result['orderPoint'],
                    'commodityCountCheck' => $result['commodityCountCheck'],
                    'minOrderCount' => $result['minOrderCount'],
                    'dayLoading' => $result['dayLoading'],
                    'speedAccess' => $result['speedAccess'],
                    'withoutTax' => $result['withoutTax'],
                    'barcodes' => $result['barcodes'],
                    'unit' => $result['unit'] ?? '',
                    'count' => (int) $result['totalCount'],
                    'totalPrice' => (float) $result['totalPrice'] // مجموع قیمت فروش
                ];
            }, $results);

            return $this->json($topCommodities);
        } catch (\Exception $e) {
            $logger->error('Error in top-selling commodities by price query', [
                'message' => $e->getMessage(),
                'sql' => $queryBuilder->getQuery()->getSQL(),
                'params' => $queryBuilder->getQuery()->getParameters()->toArray(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/report/person/buysell/export/pdf', name: 'app_report_person_buysell_export_pdf')]
    public function app_report_person_buysell_export_pdf(Provider $provider, Access $access, Request $request, EntityManagerInterface $entityManagerInterface): BinaryFileResponse|JsonResponse|StreamedResponse
    {
        $acc = $access->hasRole('report');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        $items = [];
        foreach ($params['items'] as $param) {
            $prs = $entityManagerInterface->getRepository(HesabdariRow::class)->findOneBy([
                'id' => $param['rowId'],
                'bid' => $acc['bid']
            ]);
            if ($prs)
                $items[] = $prs;
        }

        // دریافت اطلاعات شخص از اولین آیتم
        $person = null;
        if (count($items) > 0) {
            foreach ($items[0]->getDoc()->getHesabdariRows() as $row) {
                if ($row->getPerson()) {
                    $person = $row->getPerson();
                    break;
                }
            }
        }

        $response = [];
        foreach ($items as $item) {
            $temp = [
                'id' => $item->getCommodity()->getId(),
                'code' => $item->getCommodity()->getCode(),
                'khadamat' => $item->getCommodity()->isKhadamat(),
                'name' => $item->getCommodity()->getName(),
                'unit' => $item->getCommodity()->getUnit()->getName(),
                'count' => $item->getCommdityCount(),
                'date' => $item->getDoc()->getDate(),
                'docCode' => $item->getDoc()->getCode(),
                'type' => $item->getDoc()->getType()
            ];
            if ($item->getDoc()->getType() == 'buy') {
                $temp['priceAll'] = $item->getBd();
            } elseif ($item->getDoc()->getType() == 'sell') {
                $temp['priceAll'] = $item->getBs();
            }
            if ($temp['count'] != 0) {
                $temp['priceOne'] = $temp['priceAll'] / $temp['count'];
                $temp['priceAll'] = number_format($temp['priceAll']);
                $temp['priceOne'] = number_format($temp['priceOne']);
                $temp['count'] = number_format($temp['count']);
                $response[] = $temp;
            }
        }

        // اضافه کردن شماره ردیف به داده‌ها
        $responseWithRow = [];
        foreach ($response as $index => $item) {
            $responseWithRow[] = [
                'row' => $index + 1,
                'code' => $item['code'],
                'name' => $item['name'],
                'unit' => $item['unit'],
                'count' => $item['count'],
                'priceOne' => $item['priceOne'],
                'priceAll' => $item['priceAll'],
                'date' => $item['date'],
                'docCode' => $item['docCode'],
                'type' => $item['type'],
                'khadamat' => $item['khadamat']
            ];
        }

        // دریافت تنظیمات چاپ
        $printSettings = $entityManagerInterface->getRepository(PrintOptions::class)->findOneBy(['bid' => $acc['bid']]);
        
        // تنظیم مقادیر پیش‌فرض از تنظیمات ذخیره شده
        $defaultOptions = [
            'note' => $printSettings ? $printSettings->isSellNote() : true,
            'bidInfo' => $printSettings ? $printSettings->isSellBidInfo() : true,
            'taxInfo' => $printSettings ? $printSettings->isSellTaxInfo() : true,
            'discountInfo' => $printSettings ? $printSettings->isSellDiscountInfo() : true,
            'pays' => $printSettings ? $printSettings->isSellPays() : true,
            'paper' => $printSettings ? $printSettings->getSellPaper() : 'A4-L',
            'invoiceIndex' => $printSettings ? $printSettings->isSellInvoiceIndex() : true,
            'businessStamp' => $printSettings ? $printSettings->isSellBusinessStamp() : true
        ];
        
        // اولویت با پارامترهای ارسالی است
        $printOptions = array_merge($defaultOptions, $params['printOptions'] ?? []);

        $pdfPid = $provider->createPrint(
            $acc['bid'],
            $this->getUser(),
            $this->renderView('pdf/printers/buysell_report.html.twig', [
                'bid' => $acc['bid'],
                'items' => $responseWithRow,
                'printOptions' => $printOptions,
                'note' => $printSettings ? $printSettings->getSellNoteString() : '',
                'person' => $person
            ]),
            false,
            $printOptions['paper']
        );

        return $this->json(['id' => $pdfPid]);
    }
}