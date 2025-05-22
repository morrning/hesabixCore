<?php

namespace App\Controller\Componenets;

use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Service\Access;
use App\Service\Explore;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DocsearchController extends AbstractController
{
    #[Route('/api/componenets/doc/search', name: 'app_componenets_doc_search', methods: ['POST'])]
    public function search(Request $request, Access $access, EntityManagerInterface $entityManager): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        // بررسی دسترسی کاربر
        $acc = $access->hasRole('join');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        // ایجاد کوئری بیس
        $qb = $entityManager->createQueryBuilder();
        $qb->select('d')
           ->addSelect('p.name as personName')
           ->addSelect('p.nikename as personNikename')
           ->from(HesabdariDoc::class, 'd')
           ->leftJoin('d.hesabdariRows', 'r')
           ->leftJoin('r.person', 'p')
           ->where('d.bid = :bid')
           ->andWhere('d.year = :year')
           ->andWhere('d.money = :money')
           ->setParameter('bid', $acc['bid'])
           ->setParameter('year', $acc['year'])
           ->setParameter('money', $acc['money']);

        // اعمال فیلترهای جستجو
        if (isset($params['search']) && !empty($params['search'])) {
            $search = $params['search'];
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('d.code', ':search'),
                    $qb->expr()->like('d.des', ':search'),
                    $qb->expr()->like('p.name', ':search'),
                    $qb->expr()->like('p.nikename', ':search')
                )
            )
            ->setParameter('search', '%' . $search . '%');
        }

        // فیلتر بر اساس نوع سند
        if (isset($params['docType']) && !empty($params['docType'])) {
            $qb->andWhere('d.type = :type')
               ->setParameter('type', $params['docType']);
        }

        // فیلتر بر اساس تاریخ
        if (isset($params['dateFrom']) && !empty($params['dateFrom'])) {
            $qb->andWhere('d.date >= :dateFrom')
               ->setParameter('dateFrom', $params['dateFrom']);
        }
        if (isset($params['dateTo']) && !empty($params['dateTo'])) {
            $qb->andWhere('d.date <= :dateTo')
               ->setParameter('dateTo', $params['dateTo']);
        }

        // مرتب‌سازی
        $qb->orderBy('d.code', 'DESC');

        // صفحه‌بندی
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $itemsPerPage = isset($params['itemsPerPage']) ? (int)$params['itemsPerPage'] : 10;
        $qb->setFirstResult(($page - 1) * $itemsPerPage)
           ->setMaxResults($itemsPerPage);

        // اجرای کوئری
        $results = $qb->getQuery()->getResult();

        // آماده‌سازی نتایج
        $formattedResults = [];
        foreach ($results as $result) {
            $doc = $result[0]; // اولین عنصر آرایه، شیء HesabdariDoc است
            $temp = [
                'id' => $doc->getId(),
                'code' => $doc->getCode(),
                'date' => $doc->getDate(),
                'dateSubmit' => $doc->getDateSubmit(),
                'type' => $doc->getType(),
                'des' => $doc->getDes(),
                'amount' => $doc->getAmount(),
                'submitter' => $doc->getSubmitter()->getFullName(),
                'status' => 'بدون تراکنش دریافت/پرداخت',
                'personName' => $result['personName'] ?? null,
                'personNikename' => $result['personNikename'] ?? null,
                'relatedDocs' => []
            ];

            // محاسبه وضعیت تسویه و اضافه کردن اسناد مرتبط
            $pays = 0;
            foreach ($doc->getRelatedDocs() as $relatedDoc) {
                $pays += $relatedDoc->getAmount();
                $temp['relatedDocs'][] = [
                    'id' => $relatedDoc->getId(),
                    'code' => $relatedDoc->getCode(),
                    'date' => $relatedDoc->getDate(),
                    'amount' => $relatedDoc->getAmount(),
                    'type' => $relatedDoc->getType()
                ];
            }
            
            if ($pays > 0) {
                if ($doc->getAmount() <= $pays) {
                    $temp['status'] = 'تسویه شده';
                } else {
                    $temp['status'] = 'تسویه نشده';
                }
            }

            // اضافه کردن اطلاعات مشتری یا کالا
            $mainRow = $entityManager->getRepository(HesabdariRow::class)->getNotEqual($doc, 'person');
            if ($mainRow && $mainRow->getPerson()) {
                $temp['person'] = Explore::ExplorePerson($mainRow->getPerson());
            }

            // اضافه کردن برچسب فاکتور
            if ($doc->getInvoiceLabel()) {
                $temp['label'] = [
                    'code' => $doc->getInvoiceLabel()->getCode(),
                    'label' => $doc->getInvoiceLabel()->getLabel()
                ];
            }

            $formattedResults[] = $temp;
        }

        // محاسبه تعداد کل نتایج
        $countQb = clone $qb;
        $countQb->select('COUNT(d.id)');
        $totalItems = $countQb->getQuery()->getSingleScalarResult();

        return $this->json([
            'items' => $formattedResults,
            'total' => $totalItems,
            'page' => $page,
            'itemsPerPage' => $itemsPerPage
        ]);
    }

    #[Route('/api/componenets/doc/get/{code}', name: 'app_componenets_doc_get', methods: ['GET'])]
    public function getDoc(string $code, Access $access, EntityManagerInterface $entityManager): JsonResponse
    {
        // بررسی دسترسی کاربر
        $acc = $access->hasRole('join');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        // دریافت سند
        $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
            'code' => $code,
            'bid' => $acc['bid'],
            'year' => $acc['year'],
            'money' => $acc['money']
        ]);

        if (!$doc) {
            throw $this->createNotFoundException('سند مورد نظر یافت نشد');
        }

        // آماده‌سازی اطلاعات سند
        $result = [
            'id' => $doc->getId(),
            'code' => $doc->getCode(),
            'date' => $doc->getDate(),
            'dateSubmit' => $doc->getDateSubmit(),
            'type' => $doc->getType(),
            'des' => $doc->getDes(),
            'amount' => $doc->getAmount(),
            'submitter' => $doc->getSubmitter()->getFullName(),
            'status' => 'بدون تراکنش دریافت/پرداخت',
            'relatedDocs' => []
        ];

        // محاسبه وضعیت تسویه و اضافه کردن اسناد مرتبط
        $pays = 0;
        foreach ($doc->getRelatedDocs() as $relatedDoc) {
            $pays += $relatedDoc->getAmount();
            $result['relatedDocs'][] = [
                'id' => $relatedDoc->getId(),
                'code' => $relatedDoc->getCode(),
                'date' => $relatedDoc->getDate(),
                'amount' => $relatedDoc->getAmount(),
                'type' => $relatedDoc->getType()
            ];
        }
        
        if ($pays > 0) {
            if ($doc->getAmount() <= $pays) {
                $result['status'] = 'تسویه شده';
            } else {
                $result['status'] = 'تسویه نشده';
            }
        }

        // اضافه کردن اطلاعات مشتری یا کالا
        $mainRow = $entityManager->getRepository(HesabdariRow::class)->getNotEqual($doc, 'person');
        if ($mainRow && $mainRow->getPerson()) {
            $result['person'] = Explore::ExplorePerson($mainRow->getPerson());
        }

        // اضافه کردن برچسب فاکتور
        if ($doc->getInvoiceLabel()) {
            $result['label'] = [
                'code' => $doc->getInvoiceLabel()->getCode(),
                'label' => $doc->getInvoiceLabel()->getLabel()
            ];
        }

        return $this->json($result);
    }
}
