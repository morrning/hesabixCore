<?php

namespace App\Controller;

use App\Service\Access;
use App\Service\Jdate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Service\Provider;
use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Entity\HesabdariTable;
use App\Entity\BankAccount;
use App\Entity\Cashdesk;
use App\Entity\Salary;
use App\Entity\Person;
use App\Service\Log;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\HesabdariTableRepository;

class IncomeController extends AbstractController
{
    #[Route('/api/income/dashboard/data', name: 'app_income_dashboard_data', methods: ['GET'])]
    public function getIncomeDashboardData(
        Request $request,
        Access $access,
        Jdate $jdate,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // بررسی دسترسی کاربر
        $acc = $access->hasRole('income');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        // تاریخ‌های شمسی برای امروز، هفته و ماه
        $today = $jdate->jdate('Y/m/d', time());
        $weekStart = $jdate->jdate('Y/m/d', strtotime('-6 days'));
        $monthStart = $jdate->jdate('Y/m/01', time());

        // تاریخ شروع و پایان سال مالی از $acc['year'] و تبدیل به شمسی
        $yearStartUnix = (int) $acc['year']->getStart();
        $yearEndUnix = (int) $acc['year']->getEnd();
        $yearStart = $jdate->jdate('Y/m/d', $yearStartUnix);
        $yearEnd = $jdate->jdate('Y/m/d', $yearEndUnix);

        // کوئری پایه - جمع bs را محاسبه می‌کنیم
        $qb = $entityManager->createQueryBuilder()
            ->select('SUM(COALESCE(r.bs, 0)) as total')
            ->from('App\Entity\HesabdariDoc', 'd')
            ->join('d.hesabdariRows', 'r')
            ->where('d.bid = :bid')
            ->andWhere('d.money = :money')
            ->andWhere('d.type = :type')
            ->andWhere('d.year = :year')
            ->andWhere('r.bs != 0') // فقط ردیف‌هایی که bs صفر نیست
            ->setParameter('bid', $acc['bid'])
            ->setParameter('money', $acc['money'])
            ->setParameter('type', 'income')
            ->setParameter('year', $acc['year']);

        // درآمد امروز
        $todayIncome = (clone $qb)
            ->andWhere('d.date = :date')
            ->setParameter('date', $today)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        // درآمد این هفته
        $weekIncome = (clone $qb)
            ->andWhere('d.date BETWEEN :start AND :end')
            ->setParameter('start', $weekStart)
            ->setParameter('end', $today)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        // درآمد این ماه
        $monthIncome = (clone $qb)
            ->andWhere('d.date BETWEEN :start AND :end')
            ->setParameter('start', $monthStart)
            ->setParameter('end', $today)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        // درآمد سال مالی
        $yearIncome = (clone $qb)
            ->andWhere('d.date BETWEEN :start AND :end')
            ->setParameter('start', $yearStart)
            ->setParameter('end', $yearEnd)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        return $this->json([
            'today' => (int) $todayIncome,
            'week' => (int) $weekIncome,
            'month' => (int) $monthIncome,
            'year' => (int) $yearIncome,
        ]);
    }

    #[Route('/api/income/top-centers', name: 'app_income_top_centers', methods: ['GET'])]
    public function getTopIncomeCenters(
        Request $request,
        Access $access,
        Jdate $jdate,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // بررسی دسترسی کاربر
        $acc = $access->hasRole('income');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        // پارامترهای درخواست
        $period = $request->query->get('period', 'today'); // پیش‌فرض: امروز
        $limit = (int) $request->query->get('limit', 10); // پیش‌فرض: 10

        // تاریخ‌های شمسی برای امروز و ماه
        $today = $jdate->jdate('Y/m/d', time());
        $monthStart = $jdate->jdate('Y/m/01', time());

        // کوئری پایه
        $qb = $entityManager->createQueryBuilder()
            ->select('t.name AS center_name, SUM(COALESCE(r.bs, 0)) AS total_income')
            ->from('App\Entity\HesabdariDoc', 'd')
            ->join('d.hesabdariRows', 'r')
            ->join('r.ref', 't') // ارتباط با HesabdariTable
            ->where('d.bid = :bid')
            ->andWhere('d.money = :money')
            ->andWhere('d.type = :type')
            ->andWhere('d.year = :year')
            ->andWhere('r.bs != 0') // فقط ردیف‌هایی که bs صفر نیست
            ->groupBy('t.id, t.name')
            ->orderBy('total_income', 'DESC')
            ->setParameter('bid', $acc['bid'])
            ->setParameter('money', $acc['money'])
            ->setParameter('type', 'income')
            ->setParameter('year', $acc['year']);

        // اعمال فیلتر تاریخ فقط برای امروز و ماه
        if ($period === 'today') {
            $qb->andWhere('d.date = :date')
                ->setParameter('date', $today);
        } elseif ($period === 'month') {
            $qb->andWhere('d.date BETWEEN :start AND :end')
                ->setParameter('start', $monthStart)
                ->setParameter('end', $today);
        }
        // برای 'year' نیازی به شرط تاریخ نیست، چون year و type کافی است

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        $results = $qb->getQuery()->getResult();

        // آماده‌سازی داده‌ها برای نمودار
        $labels = [];
        $series = [];
        foreach ($results as $row) {
            $labels[] = $row['center_name'];
            $series[] = (int) $row['total_income'];
        }

        return $this->json([
            'labels' => $labels,
            'series' => $series,
        ]);
    }

    #[Route('/api/income/list/search', name: 'app_income_list_search', methods: ['POST'])]
    public function searchIncomeList(
        Request $request,
        Access $access,
        EntityManagerInterface $entityManager,
        HesabdariTableRepository $hesabdariTableRepository,
        Jdate $jdate
    ): JsonResponse {
        $acc = $access->hasRole('income');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        $params = json_decode($request->getContent(), true) ?? [];

        // Input parameters
        $filters = $params['filters'] ?? [];
        $pagination = $params['pagination'] ?? ['page' => 1, 'limit' => 10];
        $sort = $params['sort'] ?? ['sortBy' => 'id', 'sortDesc' => true];
        $type = $params['type'] ?? 'income';

        // Set pagination parameters
        $page = max(1, $pagination['page'] ?? 1);
        $limit = max(1, min(100, $pagination['limit'] ?? 10));

        // Build base query
        $queryBuilder = $entityManager->createQueryBuilder()
            ->select('DISTINCT d.id, d.dateSubmit, d.date, d.type, d.code, d.des, d.amount')
            ->addSelect('u.fullName as submitter')
            ->from('App\Entity\HesabdariDoc', 'd')
            ->leftJoin('d.submitter', 'u')
            ->leftJoin('d.hesabdariRows', 'r')
            ->leftJoin('r.ref', 't')
            ->where('d.bid = :bid')
            ->andWhere('d.year = :year')
            ->andWhere('d.type = :type')
            ->andWhere('d.money = :money')
            ->setParameter('bid', $acc['bid'])
            ->setParameter('year', $acc['year'])
            ->setParameter('type', $type)
            ->setParameter('money', $acc['money']);

        // Apply filters
        if (!empty($filters)) {
            // Text search
            if (isset($filters['search'])) {
                $searchValue = is_array($filters['search']) ? $filters['search']['value'] : $filters['search'];
                $queryBuilder->leftJoin('r.person', 'p')
                    ->andWhere(
                        $queryBuilder->expr()->orX(
                            'd.code LIKE :search',
                            'd.des LIKE :search',
                            'd.date LIKE :search',
                            'd.amount LIKE :search',
                            'p.nikename LIKE :search',
                            't.name LIKE :search',
                            't.code LIKE :search'
                        )
                    )
                    ->setParameter('search', "%{$searchValue}%");
            }

            // Income center filter
            if (isset($filters['account']) && $filters['account'] !== '66') {
                $accountCodes = $hesabdariTableRepository->findAllSubAccountCodes($filters['account'], $acc['bid']->getId());
                if (!empty($accountCodes)) {
                    $queryBuilder->andWhere('t.code IN (:accountCodes)')
                        ->setParameter('accountCodes', $accountCodes);
                } else {
                    $queryBuilder->andWhere('1 = 0');
                }
            }

            // Time filter
            if (isset($filters['timeFilter'])) {
                $today = $jdate->jdate('Y/m/d', time());
                switch ($filters['timeFilter']) {
                    case 'today':
                        $queryBuilder->andWhere('d.date = :today')
                            ->setParameter('today', $today);
                        break;
                    case 'week':
                        $weekStart = $jdate->jdate('Y/m/d', strtotime('-6 days'));
                        $queryBuilder->andWhere('d.date BETWEEN :weekStart AND :today')
                            ->setParameter('weekStart', $weekStart)
                            ->setParameter('today', $today);
                        break;
                    case 'month':
                        $monthStart = $jdate->jdate('Y/m/01', time());
                        $queryBuilder->andWhere('d.date BETWEEN :monthStart AND :today')
                            ->setParameter('monthStart', $monthStart)
                            ->setParameter('today', $today);
                        break;
                    case 'custom':
                        if (isset($filters['date']) && isset($filters['date']['from']) && isset($filters['date']['to'])) {
                            $queryBuilder->andWhere('d.date BETWEEN :dateFrom AND :dateTo')
                                ->setParameter('dateFrom', $filters['date']['from'])
                                ->setParameter('dateTo', $filters['date']['to']);
                        }
                        break;
                }
            }

            // Amount filter
            if (isset($filters['amount'])) {
                $queryBuilder->andWhere('d.amount = :amount')
                    ->setParameter('amount', $filters['amount']);
            }
        }

        // Apply sorting
        $sortField = is_array($sort['sortBy']) ? ($sort['sortBy']['key'] ?? 'id') : ($sort['sortBy'] ?? 'id');
        $sortDirection = ($sort['sortDesc'] ?? true) ? 'DESC' : 'ASC';
        $queryBuilder->orderBy("d.$sortField", $sortDirection);

        // Calculate total items
        $totalItemsQuery = clone $queryBuilder;
        $totalItems = $totalItemsQuery->select('COUNT(DISTINCT d.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Apply pagination
        $queryBuilder->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

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
            ];

            // Get income center details
            $incomeDetails = $entityManager->createQueryBuilder()
                ->select('t.name as center_name, t.code as center_code, r.bs as amount, r.des as des')
                ->from('App\Entity\HesabdariRow', 'r')
                ->join('r.ref', 't')
                ->where('r.doc = :docId')
                ->andWhere('r.bs != 0')
                ->setParameter('docId', $doc['id'])
                ->getQuery()
                ->getResult();

            $item['incomeCenters'] = array_map(function ($detail) {
                return [
                    'name' => $detail['center_name'],
                    'code' => $detail['center_code'],
                    'amount' => (int) $detail['amount'],
                    'des' => $detail['des'],
                ];
            }, $incomeDetails);

            // Get related person info
            $personInfo = $entityManager->createQueryBuilder()
                ->select('p.id, p.nikename, p.code')
                ->from('App\Entity\HesabdariRow', 'r')
                ->join('r.person', 'p')
                ->where('r.doc = :docId')
                ->andWhere('r.person IS NOT NULL')
                ->setParameter('docId', $doc['id'])
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            $item['person'] = $personInfo ? [
                'id' => $personInfo['id'],
                'nikename' => $personInfo['nikename'],
                'code' => $personInfo['code'],
            ] : null;

            $dataTemp[] = $item;
        }

        return $this->json([
            'items' => $dataTemp,
            'total' => (int) $totalItems,
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    #[Route('/api/incomes/list/print', name: 'app_incomes_list_print')]
    public function app_incomes_list_print(
        Provider $provider,
        Request $request,
        Access $access,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $acc = $access->hasRole('income');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        $params = json_decode($request->getContent(), true) ?? [];

        // دریافت آیتم‌های انتخاب شده یا همه آیتم‌ها
        if (!isset($params['items'])) {
            $items = $entityManager->getRepository(HesabdariDoc::class)->findBy([
                'bid' => $acc['bid'],
                'type' => 'income',
                'year' => $acc['year'],
                'money' => $acc['money']
            ]);
        } else {
            $items = [];
            foreach ($params['items'] as $param) {
                $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                    'id' => $param['id'],
                    'bid' => $acc['bid'],
                    'type' => 'income',
                    'year' => $acc['year'],
                    'money' => $acc['money']
                ]);
                if ($doc) {
                    $items[] = $doc;
                }
            }
        }

        $pid = $provider->createPrint(
            $acc['bid'],
            $this->getUser(),
            $this->renderView('pdf/incomes.html.twig', [
                'page_title' => 'فهرست درآمدها',
                'bid' => $acc['bid'],
                'items' => $items
            ])
        );

        return $this->json(['id' => $pid]);
    }

    #[Route('/api/incomes/list/excel', name: 'app_incomes_list_excel')]
    public function app_incomes_list_excel(
        Provider $provider,
        Request $request,
        Access $access,
        EntityManagerInterface $entityManager
    ): BinaryFileResponse {
        $acc = $access->hasRole('income');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        $params = json_decode($request->getContent(), true) ?? [];

        // دریافت آیتم‌های انتخاب شده یا همه آیتم‌ها
        if (!isset($params['items'])) {
            $items = $entityManager->getRepository(HesabdariDoc::class)->findBy([
                'bid' => $acc['bid'],
                'type' => 'income',
                'year' => $acc['year'],
                'money' => $acc['money']
            ]);
        } else {
            $items = [];
            foreach ($params['items'] as $param) {
                $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                    'id' => $param['id'],
                    'bid' => $acc['bid'],
                    'type' => 'income',
                    'year' => $acc['year'],
                    'money' => $acc['money']
                ]);
                if ($doc) {
                    $items[] = $doc;
                }
            }
        }

        // ایجاد فایل اکسل
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);

        // تنظیم هدرها
        $sheet->setCellValue('A1', 'ردیف')
            ->setCellValue('B1', 'شماره سند')
            ->setCellValue('C1', 'تاریخ')
            ->setCellValue('D1', 'شرح')
            ->setCellValue('E1', 'مرکز درآمد')
            ->setCellValue('F1', 'مرکز دریافت')
            ->setCellValue('G1', 'مبلغ (ریال)');

        // پر کردن داده‌ها
        $rowNumber = 2;
        foreach ($items as $index => $item) {
            // محاسبه مراکز درآمد
            $incomeCenters = [];
            foreach ($item->getHesabdariRows() as $row) {
                if ($row->getRef()) {
                    $incomeCenters[] = $row->getRef()->getName();
                }
            }
            $incomeCenterNames = implode('، ', array_unique($incomeCenters));

            // محاسبه مرکز دریافت
            $receiveCenter = null;
            foreach ($item->getHesabdariRows() as $row) {
                if (!$receiveCenter) {
                    if ($row->getBank()) {
                        $receiveCenter = $row->getBank()->getName();
                    } elseif ($row->getCashdesk()) {
                        $receiveCenter = $row->getCashdesk()->getName();
                    } elseif ($row->getSalary()) {
                        $receiveCenter = $row->getSalary()->getName();
                    } elseif ($row->getPerson()) {
                        $receiveCenter = $row->getPerson()->getNikename();
                    }
                }
            }

            $sheet->setCellValue('A' . $rowNumber, $index + 1)
                ->setCellValue('B' . $rowNumber, $item->getCode())
                ->setCellValue('C' . $rowNumber, $item->getDate())
                ->setCellValue('D' . $rowNumber, $item->getDes())
                ->setCellValue('E' . $rowNumber, $incomeCenterNames)
                ->setCellValue('F' . $rowNumber, $receiveCenter)
                ->setCellValue('G' . $rowNumber, number_format($item->getAmount()));
            $rowNumber++;
        }

        // ذخیره فایل اکسل
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filePath = __DIR__ . '/../../var/' . uniqid() . '.xlsx';
        $writer->save($filePath);

        return new BinaryFileResponse($filePath);
    }

    #[Route('/api/income/doc/insert', name: 'app_income_doc_insert', methods: ['POST'])]
    public function insertIncomeDoc(
        Request $request,
        Access $access,
        EntityManagerInterface $entityManager,
        Provider $provider,
        Log $log,
        Jdate $jdate
    ): JsonResponse {
        $acc = $access->hasRole('income');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        $params = json_decode($request->getContent(), true) ?? [];

        // بررسی پارامترهای ضروری
        if (!isset($params['rows']) || count($params['rows']) < 2) {
            return $this->json(['result' => 0, 'message' => 'حداقل دو ردیف برای سند درآمد الزامی است'], 400);
        }

        if (!isset($params['date']) || !isset($params['des'])) {
            return $this->json(['result' => 0, 'message' => 'تاریخ و شرح سند الزامی است'], 400);
        }

        // تنظیم نوع سند به income
        $params['type'] = 'income';

        // بررسی وجود سند برای ویرایش
        if (isset($params['update']) && $params['update'] != '') {
            $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                'bid' => $acc['bid'],
                'year' => $acc['year'],
                'code' => $params['update'],
                'money' => $acc['money']
            ]);
            if (!$doc) {
                return $this->json(['result' => 0, 'message' => 'سند مورد نظر یافت نشد'], 404);
            }
        }

        // ایجاد سند جدید
        $doc = new HesabdariDoc();
        $doc->setBid($acc['bid']);
        $doc->setYear($acc['year']);
        $doc->setDes($params['des']);
        $doc->setDateSubmit(time());
        $doc->setType('income');
        $doc->setDate($params['date']);
        $doc->setSubmitter($this->getUser());
        $doc->setMoney($acc['money']);
        $doc->setCode($provider->getAccountingCode($acc['bid'], 'accounting'));

        $entityManager->persist($doc);
        $entityManager->flush();

        // پردازش ردیف‌های سند
        $amount = 0;
        foreach ($params['rows'] as $row) {
            $row['bs'] = str_replace(',', '', $row['bs']);
            $row['bd'] = str_replace(',', '', $row['bd']);

            $hesabdariRow = new HesabdariRow();
            $hesabdariRow->setBid($acc['bid']);
            $hesabdariRow->setYear($acc['year']);
            $hesabdariRow->setDoc($doc);
            $hesabdariRow->setBs($row['bs']);
            $hesabdariRow->setBd($row['bd']);

            // تنظیم مرکز درآمد
            $ref = $entityManager->getRepository(HesabdariTable::class)->findOneBy([
                'code' => $row['table']
            ]);
            $hesabdariRow->setRef($ref);

            // تنظیم مرکز دریافت (بانک، صندوق، تنخواه، شخص)
            if ($row['type'] == 'bank') {
                $bank = $entityManager->getRepository(BankAccount::class)->findOneBy([
                    'id' => $row['id'],
                    'bid' => $acc['bid']
                ]);
                if (!$bank) {
                    return $this->json(['result' => 0, 'message' => 'حساب بانکی مورد نظر یافت نشد'], 404);
                }
                $hesabdariRow->setBank($bank);
            } elseif ($row['type'] == 'cashdesk') {
                $cashdesk = $entityManager->getRepository(Cashdesk::class)->find($row['id']);
                if (!$cashdesk) {
                    return $this->json(['result' => 0, 'message' => 'صندوق مورد نظر یافت نشد'], 404);
                }
                $hesabdariRow->setCashdesk($cashdesk);
            } elseif ($row['type'] == 'salary') {
                $salary = $entityManager->getRepository(Salary::class)->find($row['id']);
                if (!$salary) {
                    return $this->json(['result' => 0, 'message' => 'تنخواه مورد نظر یافت نشد'], 404);
                }
                $hesabdariRow->setSalary($salary);
            } elseif ($row['type'] == 'person') {
                $person = $entityManager->getRepository(Person::class)->findOneBy([
                    'id' => $row['id'],
                    'bid' => $acc['bid']
                ]);
                if (!$person) {
                    return $this->json(['result' => 0, 'message' => 'شخص مورد نظر یافت نشد'], 404);
                }
                $hesabdariRow->setPerson($person);
            }

            if (isset($row['des'])) {
                $hesabdariRow->setDes($row['des']);
            }

            $entityManager->persist($hesabdariRow);
            $amount += $row['bs'];
        }

        $doc->setAmount($amount);
        $entityManager->persist($doc);
        $entityManager->flush();

        $log->insert(
            'حسابداری',
            'سند درآمد شماره ' . $doc->getCode() . ' ثبت شد.',
            $this->getUser(),
            $acc['bid'],
            $doc
        );

        return $this->json([
            'result' => 1,
            'doc' => $provider->Entity2Array($doc, 0)
        ]);
    }
}