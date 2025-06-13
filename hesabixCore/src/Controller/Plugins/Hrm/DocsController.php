<?php

namespace App\Controller\Plugins\Hrm;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\PlugHrmDoc;
use App\Entity\PlugHrmDocItem;
use App\Entity\Person;
use App\Service\Access;
use App\Service\Log;

class DocsController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/hrm/docs/list', name: 'hrm_docs_list', methods: ['POST'])]
    public function list(Request $request, Access $access): JsonResponse
    {
        try {
            $params = [];
            if ($content = $request->getContent()) {
                $params = json_decode($content, true);
            }

            $acc = $access->hasRole('hrm');
            
            // دریافت پارامترهای فیلتر
            $page = $params['page'] ?? 1;
            $limit = $params['limit'] ?? 20;
            $search = $params['search'] ?? '';
            $fromDate = $params['fromDate'] ?? null;
            $toDate = $params['toDate'] ?? null;
            $personId = $params['personId'] ?? null;

            // ایجاد کوئری
            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('d')
               ->from(PlugHrmDoc::class, 'd')
               ->where('d.business = :bid')
               ->setParameter('bid', $acc['bid']);

            // اعمال فیلترها
            if ($search) {
                $qb->andWhere('d.description LIKE :search')
                   ->setParameter('search', '%' . $search . '%');
            }

            if ($fromDate) {
                $qb->andWhere('d.date >= :fromDate')
                   ->setParameter('fromDate', $fromDate);
            }

            if ($toDate) {
                $qb->andWhere('d.date <= :toDate')
                   ->setParameter('toDate', $toDate);
            }

            if ($personId) {
                $qb->andWhere('d.person = :personId')
                   ->setParameter('personId', $personId);
            }

            // محاسبه تعداد کل رکوردها
            $countQb = clone $qb;
            $countQb->select('COUNT(d.id)');
            $total = $countQb->getQuery()->getSingleScalarResult();

            // اعمال مرتب‌سازی و صفحه‌بندی
            $qb->orderBy('d.date', 'DESC')
               ->setFirstResult(($page - 1) * $limit)
               ->setMaxResults($limit);

            $docs = $qb->getQuery()->getResult();

            // تبدیل نتایج به آرایه
            $result = [];
            foreach ($docs as $doc) {
                $result[] = [
                    'id' => $doc->getId(),
                    'date' => $doc->getDate(),
                    'description' => $doc->getDescription(),
                    'creator' => $doc->getCreator() ? [
                        'id' => $doc->getCreator()->getId(),
                        'name' => $doc->getCreator()->getFullName()
                    ] : null,
                    'total' => $this->calculateTotalAmount($doc),
                    'accounting_doc' => $doc->getHesabdariDoc() ? 'صدور شده' : 'صدور نشده',
                    'status' => $doc->getHesabdariDoc() ? 'تایید شده' : 'تایید نشده'
                ];
            }

            return new JsonResponse([
                'success' => true,
                'data' => $result,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'خطا در دریافت لیست اسناد: ' . $e->getMessage()], 500);
        }
    }

    private function calculateTotalAmount(PlugHrmDoc $doc): int
    {
        $total = 0;
        foreach ($doc->getItems() as $item) {
            $total += $item->getBaseSalary();
            $total += $item->getNight();
            $total += $item->getShift();
            $total += $item->getOvertime();
        }
        return $total;
    }

    #[Route('/api/hrm/docs/get/{id}', name: 'hrm_docs_get', methods: ['POST'])]
    public function get(int $id, Access $access): JsonResponse
    {
        try {
            $acc = $access->hasRole('hrm');

            $doc = $this->entityManager->getRepository(PlugHrmDoc::class)->findOneBy([
                'id' => $id,
                'business' => $acc['bid']
            ]);

            if (!$doc) {
                return new JsonResponse(['error' => 'سند مورد نظر یافت نشد'], 404);
            }

            $items = [];
            foreach ($doc->getItems() as $item) {
                $items[] = [
                    'id' => $item->getId(),
                    'person' => [
                        'id' => $item->getPerson()->getId(),
                        'name' => $item->getPerson()->getNikename(),
                        'code' => $item->getPerson()->getCode(),
                    ],
                    'baseSalary' => $item->getBaseSalary(),
                    'overtime' => $item->getOvertime(),
                    'shift' => $item->getShift(),
                    'night' => $item->getNight(),
                    'description' => $item->getDescription()
                ];
            }

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'id' => $doc->getId(),
                    'date' => $doc->getDate(),
                    'description' => $doc->getDescription(),
                    'items' => $items
                ]
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'خطا در دریافت اطلاعات سند: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/hrm/docs/insert', name: 'hrm_docs_insert', methods: ['POST'])]
    public function insert(Request $request, Access $access, Log $log): JsonResponse
    {
        try {
            $params = [];
            if ($content = $request->getContent()) {
                $params = json_decode($content, true);
            }

            $acc = $access->hasRole('hrm');

            // بررسی داده‌های ورودی
            if (empty($params['date']) || empty($params['description']) || empty($params['items'])) {
                return new JsonResponse(['error' => 'اطلاعات ناقص است'], 400);
            }

            // ایجاد سند جدید
            $doc = new PlugHrmDoc();
            $doc->setDate($params['date']);
            $doc->setCreator($this->getUser()); 
            $doc->setBusiness($acc['bid']);
            $doc->setDescription($params['description']);
            $doc->setCreateDate(time());
            // افزودن آیتم‌ها
            foreach ($params['items'] as $itemData) {
                if (empty($itemData['person'])) {
                    continue;
                }

                $item = new PlugHrmDocItem();
                $item->setPerson($this->entityManager->getReference(Person::class, $itemData['person']));
                $item->setBaseSalary($itemData['baseSalary'] ?? 0);
                $item->setOvertime($itemData['overtime'] ?? 0);
                $item->setShift($itemData['shift'] ?? 0);
                $item->setNight($itemData['night'] ?? 0);
                $item->setDescription($itemData['description'] ?? '');
                $item->setDoc($doc);

                $this->entityManager->persist($item);
            }

            $this->entityManager->persist($doc);
            $this->entityManager->flush();


            return new JsonResponse([
                'success' => true,
                'message' => 'سند با موفقیت ثبت شد',
                'data' => [
                    'id' => $doc->getId()
                ]
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'خطا در ثبت سند: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/hrm/docs/update', name: 'hrm_docs_update', methods: ['POST'])]
    public function update(Request $request): JsonResponse
    {
        // TODO: پیاده‌سازی ویرایش سند حقوق
        return new JsonResponse([]);
    }

    #[Route('/api/hrm/docs/delete', name: 'hrm_docs_delete', methods: ['POST'])]
    public function delete(Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $params = [];
            if ($content = $request->getContent()) {
                $params = json_decode($content, true);
            }
    
            $acc = $access->hasRole('hrm');
            $id = $params['id'] ?? null;

            if (!$id) {
                return new JsonResponse(['error' => 'شناسه سند الزامی است'], 400);
            }

            $doc = $entityManager->getRepository(PlugHrmDoc::class)->findOneBy([
                'id' => $id,
                'business' => $acc['bid']
            ]);
            if (!$doc) {
                return new JsonResponse(['error' => 'سند مورد نظر یافت نشد'], 404);
            }

            // حذف آیتم‌های سند
            foreach ($doc->getItems() as $item) {
                $entityManager->remove($item);
            }

            // حذف سند حسابداری در صورت وجود
            if($doc->getHesabdariDoc()){
                $entityManager->remove($doc->getHesabdariDoc());
            }
            // حذف سند
            $entityManager->remove($doc);
            $entityManager->flush();


            return new JsonResponse(['success' => true, 'message' => 'سند با موفقیت حذف شد']);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'خطا در حذف سند: ' . $e->getMessage()], 500);
        }
    }
}