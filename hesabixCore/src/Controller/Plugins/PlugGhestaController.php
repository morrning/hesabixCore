<?php

namespace App\Controller\Plugins;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\PlugGhestaDoc;
use App\Entity\PlugGhestaItem;
use App\Entity\HesabdariDoc;
use App\Entity\Person;
use App\Service\Access;
use App\Service\Provider;
use App\Service\Printers;
use App\Entity\PrintOptions;
use App\Service\Log;
use App\Entity\Business;

class PlugGhestaController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/plugins/ghesta/invoices', name: 'plugin_ghesta_invoices', methods: ['GET'])]
    public function plugin_ghesta_invoices(EntityManagerInterface $entityManager, Access $access) : JsonResponse
    {
        $acc = $access->hasRole('plugGhestaManager');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $invoices = $entityManager->getRepository(PlugGhestaDoc::class)->findBy(['bid' => $acc['bid']]);
        $data = [];
        foreach($invoices as $invoice){
            $data[] = [
                'id' => $invoice->getId(),
                'code' => $invoice->getMainDoc() ? $invoice->getMainDoc()->getCode() : null,
                'dateSubmit' => $invoice->getDateSubmit(),
                'count' => $invoice->getCount(),
                'profitPercent' => $invoice->getProfitPercent(),
                'profitAmount' => $invoice->getProfitAmount(),
                'profitType' => $invoice->getProfitType(),
                'daysPay' => $invoice->getDaysPay(),
                'person' => [
                    'id' => $invoice->getPerson()->getId(),
                    'name' => $invoice->getPerson()->getName(),
                    'nikename' => $invoice->getPerson()->getNikename()
                ]
            ];
        }
        return $this->json($data);
    }

    #[Route('/api/plugins/ghesta/invoices/{id}', name: 'plugin_ghesta_invoice', methods: ['GET'])]
    public function plugin_ghesta_invoice(EntityManagerInterface $entityManager, Access $access, $id) : JsonResponse
    {
        $acc = $access->hasRole('plugGhestaManager');
        if(!$acc)
            throw $this->createAccessDeniedException();
            
        $invoice = $entityManager->getRepository(PlugGhestaDoc::class)->findOneBy([
            'id' => $id,
            'bid' => $acc['bid']
        ]);
        
        if(!$invoice)
            throw $this->createNotFoundException();
            
        $data = [
            'id' => $invoice->getId(),
            'code' => $invoice->getMainDoc() ? $invoice->getMainDoc()->getCode() : null,
            'dateSubmit' => $invoice->getDateSubmit(),
            'count' => $invoice->getCount(),
            'profitPercent' => $invoice->getProfitPercent(),
            'profitAmount' => $invoice->getProfitAmount(),
            'profitType' => $invoice->getProfitType(),
            'daysPay' => $invoice->getDaysPay(),
            'person' => [
                'id' => $invoice->getPerson()->getId(),
                'name' => $invoice->getPerson()->getName(),
                'nikename' => $invoice->getPerson()->getNikename()
            ],
            'items' => []
        ];
        
        foreach($invoice->getPlugGhestaItems() as $item) {
            $data['items'][] = [
                'id' => $item->getId(),
                'date' => $item->getDate(),
                'amount' => $item->getAmount(),
                'num' => $item->getNum(),
                'hesabdariDoc' => $item->getHesabdariDoc() ? [
                    'id' => $item->getHesabdariDoc()->getId(),
                    'code' => $item->getHesabdariDoc()->getCode()
                ] : null
            ];
        }
        
        return $this->json($data);
    }

    #[Route('/api/plugins/ghesta/invoices/add', name: 'plugin_ghesta_invoice_add', methods: ['POST'])]
    public function plugin_ghesta_invoice_add(Request $request, EntityManagerInterface $entityManager, Access $access, Provider $provider) : JsonResponse
    {
        $acc = $access->hasRole('plugGhestaManager');
        if(!$acc)
            throw $this->createAccessDeniedException();
            
        $params = json_decode($request->getContent(), true);
        if(!$params)
            throw $this->createNotFoundException();
            
        // دریافت سند حسابداری
        $hesabdariDoc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
            'id' => $params['hesabdariDocId'],
            'bid' => $acc['bid']
        ]);
        if (!$hesabdariDoc) {
            throw $this->createNotFoundException('HesabdariDoc not found');
        }
            
        // ایجاد سند اقساط
        $doc = new PlugGhestaDoc();
        $doc->setBid($acc['bid']);
        $doc->setSubmitter($this->getUser());
        $doc->setDateSubmit(time());
        $doc->setCount($params['count']);
        $doc->setProfitPercent($params['profitPercent']);
        $doc->setProfitAmount($params['profitAmount']);
        $doc->setProfitType($params['profitType']);
        $doc->setDaysPay(floatval($params['daysPay']));
        $doc->setMainDoc($hesabdariDoc);
        
        // دریافت اطلاعات شخص از فاکتور
        $person = $entityManager->getRepository(Person::class)->findOneBy([
            'id' => $params['personId'],
            'bid' => $acc['bid']
        ]);
        if (!$person) {
            throw $this->createNotFoundException('Person not found');
        }
        $doc->setPerson($person);
        
        $entityManager->persist($doc);
        $entityManager->flush();
        
        // ایجاد اقساط
        foreach($params['items'] as $item) {
            $ghestaItem = new PlugGhestaItem();
            $ghestaItem->setDoc($doc);
            $ghestaItem->setDate($item['date']);
            $ghestaItem->setAmount($item['amount']);
            $ghestaItem->setNum($item['num']);
            
            if(isset($item['hesabdariDocId'])) {
                $hesabdariDoc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                    'id' => $item['hesabdariDocId'],
                    'bid' => $acc['bid']
                ]);
                if($hesabdariDoc) {
                    $ghestaItem->setHesabdariDoc($hesabdariDoc);
                }
            }
            
            $entityManager->persist($ghestaItem);
        }
        
        $entityManager->flush();
        
        return $this->json(['result' => 1, 'id' => $doc->getId()]);
    }

    #[Route('/api/plugins/ghesta/invoices/edit/{id}', name: 'plugin_ghesta_invoice_edit', methods: ['POST'])]
    public function plugin_ghesta_invoice_edit(Request $request, EntityManagerInterface $entityManager, Access $access, $id) : JsonResponse
    {
        $acc = $access->hasRole('plugGhestaManager');
        if(!$acc)
            throw $this->createAccessDeniedException();
            
        $doc = $entityManager->getRepository(PlugGhestaDoc::class)->findOneBy([
            'id' => $id,
            'bid' => $acc['bid']
        ]);
        
        if(!$doc)
            throw $this->createNotFoundException();
            
        $params = json_decode($request->getContent(), true);
        if(!$params)
            throw $this->createNotFoundException();
            
        // به‌روزرسانی اطلاعات سند
        $doc->setCount($params['count']);
        $doc->setProfitPercent($params['profitPercent']);
        $doc->setProfitAmount($params['profitAmount']);
        $doc->setProfitType($params['profitType']);
        $doc->setDaysPay(floatval($params['daysPay']));
        
        // دریافت اطلاعات شخص از فاکتور
        $person = $entityManager->getRepository(Person::class)->find($params['personId']);
        if (!$person) {
            throw $this->createNotFoundException('Person not found');
        }
        $doc->setPerson($person);
        
        // حذف اقساط قبلی
        foreach($doc->getPlugGhestaItems() as $item) {
            $entityManager->remove($item);
        }
        
        // ایجاد اقساط جدید
        foreach($params['items'] as $item) {
            $ghestaItem = new PlugGhestaItem();
            $ghestaItem->setDoc($doc);
            $ghestaItem->setDate($item['date']);
            $ghestaItem->setAmount($item['amount']);
            $ghestaItem->setNum($item['num']);
            
            if(isset($item['hesabdariDocId'])) {
                $hesabdariDoc = $entityManager->getRepository(HesabdariDoc::class)->find($item['hesabdariDocId']);
                if($hesabdariDoc) {
                    $ghestaItem->setHesabdariDoc($hesabdariDoc);
                }
            }
            
            $entityManager->persist($ghestaItem);
        }
        
        $entityManager->flush();
        
        return $this->json(['result' => 1]);
    }

    #[Route('/api/plugins/ghesta/invoice/{id}', name: 'plugin_ghesta_invoice_delete', methods: ['DELETE'])]
    public function plugin_ghesta_invoice_delete(EntityManagerInterface $entityManager, Access $access, $id) : JsonResponse
    {
        $acc = $access->hasRole('plugGhestaManager');
        if(!$acc)
            throw $this->createAccessDeniedException();
            
        $doc = $entityManager->getRepository(PlugGhestaDoc::class)->findOneBy([
            'id' => $id,
            'bid' => $acc['bid']
        ]);
        
        if(!$doc)
            throw $this->createNotFoundException();
            
        // حذف اقساط
        foreach($doc->getPlugGhestaItems() as $item) {
            $entityManager->remove($item);
        }
        
        $entityManager->remove($doc);
        $entityManager->flush();
        
        return $this->json(['result' => 1]);
    }

    #[Route('/api/plugins/ghesta/invoices/search', name: 'plugin_ghesta_invoice_search', methods: ['POST'])]
    public function plugin_ghesta_invoice_search(Request $request, Access $access): JsonResponse
    {
        try {
            $acc = $access->hasRole('plugGhestaManager');
            if(!$acc)
                throw $this->createAccessDeniedException();

            $params = json_decode($request->getContent(), true);
            $search = $params['search'] ?? '';
            $page = (int)($params['page'] ?? 1);
            $perPage = (int)($params['perPage'] ?? 10);
            $dateFilter = $params['dateFilter'] ?? 'all';
            $statusFilter = $params['statusFilter'] ?? 'all';
            $sortBy = $params['sortBy'] ?? [];

            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('d')
                ->from(PlugGhestaDoc::class, 'd')
                ->leftJoin('d.person', 'p')
                ->leftJoin('d.plugGhestaItems', 'i')
                ->where('d.bid = :bid')
                ->setParameter('bid', $acc['bid'])
                ->groupBy('d.id');

            // اعمال فیلتر جستجو
            if (!empty($search)) {
                $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('d.id', ':search'),
                        $qb->expr()->like('p.name', ':search'),
                        $qb->expr()->like('p.nikename', ':search')
                    )
                )->setParameter('search', '%' . $search . '%');
            }

            // اعمال فیلتر تاریخ
            if ($dateFilter !== 'all') {
                $now = new \DateTime();
                switch ($dateFilter) {
                    case 'today':
                        $qb->andWhere('DATE(d.dateSubmit) = :today')
                            ->setParameter('today', $now->format('Y-m-d'));
                        break;
                    case 'week':
                        $qb->andWhere('d.dateSubmit >= :weekStart')
                            ->setParameter('weekStart', $now->modify('-7 days')->format('Y-m-d'));
                        break;
                    case 'month':
                        $qb->andWhere('d.dateSubmit >= :monthStart')
                            ->setParameter('monthStart', $now->modify('-30 days')->format('Y-m-d'));
                        break;
                }
            }

            // اعمال فیلتر وضعیت
            if ($statusFilter !== 'all') {
                switch ($statusFilter) {
                    case 'paid':
                        $qb->andWhere('i.hesabdariDoc IS NOT NULL');
                        break;
                    case 'unpaid':
                        $qb->andWhere('i.hesabdariDoc IS NULL');
                        break;
                    case 'partial':
                        $qb->andWhere('EXISTS (SELECT 1 FROM ' . PlugGhestaItem::class . ' i2 WHERE i2.doc = d.id AND i2.hesabdariDoc IS NOT NULL)')
                            ->andWhere('EXISTS (SELECT 1 FROM ' . PlugGhestaItem::class . ' i3 WHERE i3.doc = d.id AND i3.hesabdariDoc IS NULL)');
                        break;
                }
            }

            // اعمال مرتب‌سازی
            if (!empty($sortBy)) {
                foreach ($sortBy as $sort) {
                    $field = $sort['key'];
                    $direction = $sort['order'] === 'desc' ? 'DESC' : 'ASC';
                    
                    // تبدیل نام فیلد به نام ستون در دیتابیس
                    $columnMap = [
                        'id' => 'd.id',
                        'code' => 'd.code',
                        'dateSubmit' => 'd.dateSubmit',
                        'amount' => 'd.amount',
                        'profitAmount' => 'd.profitAmount',
                        'profitPercent' => 'd.profitPercent',
                        'count' => 'd.count',
                        'profitType' => 'd.profitType'
                    ];
                    
                    if (isset($columnMap[$field])) {
                        $qb->addOrderBy($columnMap[$field], $direction);
                    }
                }
            } else {
                $qb->orderBy('d.dateSubmit', 'DESC');
            }

            // محاسبه تعداد کل رکوردها
            $countQb = clone $qb;
            $countQb->select('COUNT(DISTINCT d.id)');
            $total = (int)$countQb->getQuery()->getScalarResult();

            // اگر هیچ نتیجه‌ای وجود نداشت، آرایه خالی برگردان
            if ($total == 0) {
                return $this->json([
                    'result' => 1,
                    'items' => [],
                    'total' => 0
                ]);
            }

            // اعمال صفحه‌بندی
            $qb->setFirstResult(($page - 1) * $perPage)
                ->setMaxResults($perPage);

            $items = $qb->getQuery()->getResult();

            // تبدیل نتایج به آرایه
            $result = [];
            foreach ($items as $item) {
                $firstGhestaDate = null;
                $ghestaItems = $item->getPlugGhestaItems();
                if (count($ghestaItems) > 0) {
                    $firstGhestaDate = $ghestaItems[0]->getDate();
                }
                
                $result[] = [
                    'id' => $item->getId(),
                    'code' => $item->getMainDoc() ? $item->getMainDoc()->getCode() : null,
                    'firstGhestaDate' => $firstGhestaDate,
                    'amount' => $item->getMainDoc() ? $item->getMainDoc()->getAmount() : 0, // مبلغ کل فاکتور
                    'profitAmount' => $item->getProfitAmount(),
                    'profitPercent' => $item->getProfitPercent(),
                    'profitType' => $item->getProfitType(),
                    'count' => $item->getCount(),
                    'person' => $item->getPerson() ? [
                        'id' => $item->getPerson()->getId(),
                        'name' => $item->getPerson()->getName(),
                        'nikename' => $item->getPerson()->getNikename()
                    ] : null,
                    'mainDoc' => $item->getMainDoc() ? [
                        'id' => $item->getMainDoc()->getId(),
                        'code' => $item->getMainDoc()->getCode()
                    ] : null
                ];
            }

            return $this->json([
                'result' => 1,
                'items' => $result,
                'total' => $total
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'result' => 0,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/plugins/ghesta/print', name: 'plugin_ghesta_print', methods: ['POST'])]
    public function plugin_ghesta_print(Printers $printers, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('plugGhestaManager');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $params = json_decode($request->getContent(), true);
        $params['pdf'] = $params['pdf'] ?? true;
        
        // دریافت تنظیمات پیش‌فرض از PrintOptions
        $printSettings = $entityManager->getRepository(PrintOptions::class)->findOneBy(['bid' => $acc['bid']]);
        
        // تنظیم مقادیر پیش‌فرض از تنظیمات ذخیره شده
        $defaultOptions = [
            'note' => $printSettings ? $printSettings->isSellNote() : true,
            'bidInfo' => $printSettings ? $printSettings->isSellBidInfo() : true,
            'paper' => $printSettings ? $printSettings->getSellPaper() : 'A4-L',
            'businessStamp' => $printSettings ? $printSettings->isSellBusinessStamp() : true
        ];
        
        // اولویت با پارامترهای ارسالی است
        $printOptions = array_merge($defaultOptions, $params['printOptions'] ?? []);

        $doc = $entityManager->getRepository(PlugGhestaDoc::class)->findOneBy([
            'id' => $params['id'],
            'bid' => $acc['bid']
        ]);
        
        if (!$doc)
            throw $this->createNotFoundException();

        $pdfPid = 0;
        if ($params['pdf'] == true) {
            $note = '';
            if ($printSettings) {
                $note = $printSettings->getSellNoteString();
            }
            
            // دریافت اطلاعات کسب و کار
            $business = $entityManager->getRepository(Business::class)->find($acc['bid']);
            
            $pdfPid = $provider->createPrint(
                $acc['bid'],
                $this->getUser(),
                $this->renderView('pdf/plugins/ghesta/report.html.twig', [
                    'bid' => $business,
                    'doc' => $doc,
                    'items' => array_map(function($item) {
                        return [
                            'date' => $item->getDate(),
                            'amount' => $item->getAmount(),
                            'num' => $item->getNum(),
                            'hesabdariDoc' => $item->getHesabdariDoc()
                        ];
                    }, $doc->getPlugGhestaItems()->toArray()),
                    'person' => $doc->getPerson(),
                    'printOptions' => $printOptions,
                    'note' => $note
                ]),
                false,
                $printOptions['paper']
            );
        }

        return $this->json(['id' => $pdfPid]);
    }
}