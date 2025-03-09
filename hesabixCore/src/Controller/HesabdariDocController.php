<?php

namespace App\Controller;

use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Entity\HesabdariTable;
use App\Service\Access;
use App\Service\Extractor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class HesabdariDocController extends AbstractController
{
    private $em;
    private $access;
    private $extractor;

    public function __construct(EntityManagerInterface $em, Access $access, Extractor $extractor)
    {
        $this->em = $em;
        $this->access = $access;
        $this->extractor = $extractor;
    }

    #[Route('/hesabdari/tables', name: 'get_hesabdari_tables', methods: ['GET'])]
    public function getHesabdariTables(): JsonResponse
    {
        $accessData = $this->access->hasRole('accounting');
        if (!$accessData) {
            return new JsonResponse($this->extractor->operationFail('دسترسی ندارید'), 403);
        }

        $bid = $accessData['bid'];

        // گرفتن همه حساب‌ها (عمومی و خصوصی)
        $tables = $this->em->getRepository(HesabdariTable::class)->findBy([
            'bid' => [$bid, null], // حساب‌های عمومی (null) و خصوصی (bid)
        ]);

        // تبدیل به ساختار درختی
        $tree = $this->buildTree($tables);

        return new JsonResponse($this->extractor->operationSuccess($tree, 'لیست حساب‌ها با موفقیت دریافت شد'));
    }

    private function buildTree(array $tables): array
    {
        $tree = [];
        $map = [];

        // مپ کردن همه حساب‌ها
        foreach ($tables as $table) {
            $map[$table->getId()] = [
                'id' => $table->getId(),
                'name' => $table->getName(),
                'code' => $table->getCode(),
                'type' => $table->getType(),
                'bid' => $table->getBid(),
                'children' => [],
            ];
        }

        // ساخت درخت
        foreach ($tables as $table) {
            $node = &$map[$table->getId()];
            $upper = $table->getUpper(); // این یه شیء HesabdariTable یا null هست
            $upperId = $upper ? $upper->getId() : null; // اگه upper باشه، idش رو بگیریم

            // اگه upper برابر null یا 0 باشه، ریشه‌ست
            if ($upperId === null || $upperId === 0) {
                $tree[] = &$node;
            } elseif (isset($map[$upperId])) {
                $map[$upperId]['children'][] = &$node;
            }
        }

        return $tree;
    }

    #[Route('/hesabdari/doc', name: 'create_hesabdari_doc', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $accessData = $this->access->hasRole('accounting');
        if (!$accessData) {
            return new JsonResponse($this->extractor->operationFail('دسترسی ندارید'), 403);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['date']) || !isset($data['rows'])) {
            return new JsonResponse($this->extractor->paramsNotSend(), 400);
        }

        $doc = new HesabdariDoc();
        $doc->setBid($accessData['bid']);
        $doc->setSubmitter($accessData['user']);
        $doc->setYear($accessData['year']);
        $doc->setMoney($accessData['money']);
        $doc->setDate($data['date']);
        $doc->setDateSubmit((string) time());
        $doc->setType('doc');
        $doc->setCode($this->generateDocCode($accessData['bid']));

        $totalBd = 0;
        $totalBs = 0;
        foreach ($data['rows'] as $rowData) {
            $row = new HesabdariRow();
            $row->setDoc($doc);
            $row->setRef($this->em->getRepository('App\Entity\HesabdariTable')->find($rowData['ref']));
            $row->setBd($rowData['bd'] ?? '0');
            $row->setBs($rowData['bs'] ?? '0');
            $row->setDes($rowData['des'] ?? null);
            $row->setBid($accessData['bid']);
            $row->setYear($accessData['year']);

            $totalBd += (int) $row->getBd();
            $totalBs += (int) $row->getBs();

            $this->em->persist($row);
            $doc->addHesabdariRow($row);
        }

        if ($totalBd !== $totalBs) {
            return new JsonResponse($this->extractor->operationFail('جمع بدهکار و بستانکار باید برابر باشد'), 400);
        }

        $doc->setAmount($totalBd);
        $this->em->persist($doc);
        $this->em->flush();

        return new JsonResponse($this->extractor->operationSuccess(['id' => $doc->getId()], 'سند با موفقیت ثبت شد'));
    }

    #[Route('/hesabdari/doc/{id}', name: 'edit_hesabdari_doc', methods: ['PUT'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        $accessData = $this->access->hasRole('accounting');
        if (!$accessData) {
            return new JsonResponse($this->extractor->operationFail('دسترسی ندارید'), 403);
        }

        $doc = $this->em->getRepository(HesabdariDoc::class)->find($id);
        if (!$doc || $doc->getBid() !== $accessData['bid']) {
            return new JsonResponse($this->extractor->notFound(), 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['date']) || !isset($data['rows'])) {
            return new JsonResponse($this->extractor->paramsNotSend(), 400);
        }

        $doc->setDate($data['date']);

        foreach ($doc->getHesabdariRows() as $row) {
            $this->em->remove($row);
        }
        $doc->getHesabdariRows()->clear();

        $totalBd = 0;
        $totalBs = 0;
        foreach ($data['rows'] as $rowData) {
            $row = new HesabdariRow();
            $row->setDoc($doc);
            $row->setRef($this->em->getRepository('App\Entity\HesabdariTable')->find($rowData['ref']));
            $row->setBd($rowData['bd'] ?? '0');
            $row->setBs($rowData['bs'] ?? '0');
            $row->setDes($rowData['des'] ?? null);
            $row->setBid($accessData['bid']);
            $row->setYear($accessData['year']);

            $totalBd += (int) $row->getBd();
            $totalBs += (int) $row->getBs();

            $this->em->persist($row);
            $doc->addHesabdariRow($row);
        }

        if ($totalBd !== $totalBs) {
            return new JsonResponse($this->extractor->operationFail('جمع بدهکار و بستانکار باید برابر باشد'), 400);
        }

        $doc->setAmount($totalBd);
        $this->em->persist($doc);
        $this->em->flush();

        return new JsonResponse($this->extractor->operationSuccess(['id' => $doc->getId()], 'سند با موفقیت ویرایش شد'));
    }

    private function generateDocCode($business): string
    {
        $lastDoc = $this->em->getRepository(HesabdariDoc::class)->findOneBy(
            ['bid' => $business],
            ['code' => 'DESC']
        );
        $newCode = $lastDoc ? ((int) $lastDoc->getCode() + 1) : 1;
        return (string) $newCode;
    }
}