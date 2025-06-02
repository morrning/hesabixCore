<?php

namespace App\Controller\Plugins\Hrm;

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

class DocsController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/hrm/docs/list', name: 'hrm_docs_list', methods: ['POST'])]
    public function list(Request $request): JsonResponse
    {
        // TODO: پیاده‌سازی دریافت لیست اسناد حقوق
        return new JsonResponse([]);
    }

    #[Route('/api/hrm/docs/get/{id}', name: 'hrm_docs_get', methods: ['POST'])]
    public function get(int $id): JsonResponse
    {
        // TODO: پیاده‌سازی دریافت اطلاعات یک سند حقوق
        return new JsonResponse([]);
    }

    #[Route('/api/hrm/docs/insert', name: 'hrm_docs_insert', methods: ['POST'])]
    public function insert(Request $request): JsonResponse
    {
        // TODO: پیاده‌سازی ثبت سند حقوق جدید
        return new JsonResponse([]);
    }

    #[Route('/api/hrm/docs/update', name: 'hrm_docs_update', methods: ['POST'])]
    public function update(Request $request): JsonResponse
    {
        // TODO: پیاده‌سازی ویرایش سند حقوق
        return new JsonResponse([]);
    }

    #[Route('/api/hrm/docs/delete', name: 'hrm_docs_delete', methods: ['POST'])]
    public function delete(Request $request): JsonResponse
    {
        // TODO: پیاده‌سازی حذف سند حقوق
        return new JsonResponse([]);
    }
}