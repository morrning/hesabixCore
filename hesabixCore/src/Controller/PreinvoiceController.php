<?php

namespace App\Controller;

use App\Service\Log;
use App\Service\Access;
use App\Service\Explore;
use App\Entity\Commodity;
use App\Service\PluginService;
use App\Service\Provider;
use App\Service\Extractor;
use App\Entity\PreInvoiceDoc;
use App\Entity\PreInvoiceItem;
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

class PreinvoiceController extends AbstractController
{

    private $access;
    private $extractor;

    public function __construct(Access $access, Extractor $extractor)
    {
        $this->access = $access;
        $this->extractor = $extractor;
    }


    #[Route('/api/preinvoice/get/{id}', name: 'app_preinvoice_get')]
    public function getPreinvoice(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $acc = $this->access->hasRole('preinvoice');
        if (!$acc) {
            return new JsonResponse($this->extractor->operationFail('دسترسی ندارید'), 403);
        }
        
        $preinvoice = $entityManager->getRepository(PreInvoiceDoc::class)->findOneBy(['code' => $id, 'bid' => $acc['bid'], 'year' => $acc['year']]);
        if (!$preinvoice) {
            return new JsonResponse(['error' => 'پیش فاکتور یافت نشد'], 404);
        }
        
        $data = [
            'id' => $preinvoice->getId(),
            'code' => $preinvoice->getCode(),
            'date' => $preinvoice->getDate(),
            'des' => $preinvoice->getDes(),
            'person' => Explore::ExplorePerson($preinvoice->getPerson()),
            'amount' => $preinvoice->getAmount(),
            'taxPercent' => $preinvoice->getTaxPercent(),
            'totalDiscount' => $preinvoice->getTotalDiscount(),
            'totalDiscountPercent' => $preinvoice->getTotalDiscountPercent(),
            'shippingCost' => $preinvoice->getShippingCost(),
            'showPercentDiscount' => $preinvoice->isShowPercentDiscount(),
            'showTotalPercentDiscount' => $preinvoice->isShowTotalPercentDiscount(),
            'items' => array_map(function($item) {
                return [
                    'id' => $item->getId(),
                    'commodity' => [
                        'id' => $item->getCommodity()->getId(),
                        'name' => $item->getCommodity()->getName()
                    ],
                    'count' => $item->getCommodityCount(),
                    'price' => $item->getBs(),
                    'discountPercent' => $item->getDiscountPercent(),
                    'discountAmount' => $item->getDiscountAmount(),
                    'description' => $item->getDes(),
                    'showPercentDiscount' => $item->isShowPercentDiscount()
                ];
            }, $preinvoice->getPreInvoiceItems()->toArray())
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/preinvoice/save', name: 'app_preinvoice_save')]
    public function savePreinvoice(Access $access,Provider $provider, Log $log, EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $acc = $access->hasRole('preinvoice');
        if (!$acc) {
            return new JsonResponse($this->extractor->operationFail('دسترسی ندارید'), 403);
        }

        $data = json_decode($request->getContent(), true);
        
        if (isset($data['id']) && $data['id']) {
            $preinvoice = $entityManager->getRepository(PreInvoiceDoc::class)->findOneBy(['code' => $data['id'], 'bid' => $acc['bid'], 'year' => $acc['year']]);
            if (!$preinvoice) {
                return new JsonResponse(['error' => 'پیش فاکتور یافت نشد'], 404);
            }
        } else {
            $preinvoice = new PreInvoiceDoc();
            $preinvoice->setCode($this->generateCode($entityManager));
            $preinvoice->setSubmitter($this->getUser());
            $preinvoice->setYear($acc['year']);
            $preinvoice->setBid($acc['bid']);
            $preinvoice->setMoney($acc['money']);
            $preinvoice->setStatus(1);

            $preinvoice->setCode($provider->getAccountingCode($acc['bid'], 'accounting'));
        }

        $person = $entityManager->getRepository(Person::class)->find($data['person']);
        if (!$person) {
            return new JsonResponse(['error' => 'شخص یافت نشد'], 404);
        }

        $preinvoice->setPerson($person);
        $preinvoice->setDate($data['date']);
        $preinvoice->setDes($data['des']);
        $preinvoice->setAmount($data['amount']);
        $preinvoice->setTaxPercent($data['taxPercent']);
        $preinvoice->setTotalDiscount($data['totalDiscount']);
        $preinvoice->setTotalDiscountPercent($data['totalDiscountPercent']);
        $preinvoice->setShippingCost($data['shippingCost']);
        $preinvoice->setShowPercentDiscount($data['showPercentDiscount']);
        $preinvoice->setShowTotalPercentDiscount($data['showTotalPercentDiscount']);

        $entityManager->persist($preinvoice);
        $entityManager->flush();

        // حذف آیتم‌های قبلی
        if (isset($data['id']) && $data['id']) {
            foreach ($preinvoice->getPreInvoiceItems() as $oldItem) {
                $entityManager->remove($oldItem);
            }
        }

        // اضافه کردن آیتم‌های جدید
        foreach ($data['items'] as $itemData) {
            $item = new PreInvoiceItem();
            $commodity = $entityManager->getRepository(Commodity::class)->find($itemData['commodity']);
            if (!$commodity) {
                continue;
            }

            $item->setCommodity($commodity);
            $item->setCommodityCount($itemData['count']);
            $item->setBs($itemData['price']);
            $item->setDiscountPercent($itemData['discountPercent']);
            $item->setDiscountAmount($itemData['discountAmount']);
            $item->setDes($itemData['description']);
            $item->setShowPercentDiscount($itemData['showPercentDiscount']);
            $item->setDoc($preinvoice);
            $entityManager->persist($item);
        }

        $entityManager->flush();
        $log->insert(
            'پیش فاکتور',
            'پیش فاکتور شماره ' . $preinvoice->getCode() . ' ثبت / ویرایش شد.',
            $this->getUser(),
            $acc['bid'],
        );
        return new JsonResponse(['id' => $preinvoice->getId()]);
    }

    #[Route('/api/preinvoice/delete/{id}', name: 'app_preinvoice_delete')]
    public function deletePreinvoice(Access $access, Log $log, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $acc = $access->hasRole('preinvoice');
        if (!$acc) {
            return new JsonResponse($this->extractor->operationFail('دسترسی ندارید'), 403);
        }

        $preinvoice = $entityManager->getRepository(PreInvoiceDoc::class)->findOneBy(['code' => $id, 'bid' => $acc['bid'], 'year' => $acc['year']] );
        if (!$preinvoice) {
            return new JsonResponse(['error' => 'پیش فاکتور یافت نشد'], 404);
        }

        foreach ($preinvoice->getPreInvoiceItems() as $item) {
            $entityManager->remove($item);
        }

        $entityManager->remove($preinvoice);
        $entityManager->flush();
        $log->insert(
            'پیش فاکتور',
            'پیش فاکتور شماره ' . $preinvoice->getCode() . ' حذف شد.',
            $this->getUser(),
            $acc['bid'],
        );
        return new JsonResponse(['message' => 'پیش فاکتور با موفقیت حذف شد']);
    }

    private function generateCode(EntityManagerInterface $entityManager): string
    {
        $lastPreinvoice = $entityManager->getRepository(PreInvoiceDoc::class)
            ->findOneBy([], ['id' => 'DESC']);
        
        $lastNumber = $lastPreinvoice ? (int)substr($lastPreinvoice->getCode(), 1) : 0;
        return 'P' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    }

    #[Route('/api/preinvoice/docs/search', name: 'app_presell_search')]
    public function searchPreinvoices(Access $access, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('preinvoice');
        if (!$acc) {
            return new JsonResponse($this->extractor->operationFail('دسترسی ندارید'), 403);
        }

        $preinvoices = $entityManager->getRepository(PreInvoiceDoc::class)
            ->findBy(['bid' => $acc['bid'], 'year' => $acc['year']], ['id' => 'DESC']);

        $result = [];
        foreach ($preinvoices as $preinvoice) {
            $totalAmount = $preinvoice->getAmount() - $preinvoice->getTotalDiscount() + $preinvoice->getShippingCost();
            
            $result[] = [
                'code' => $preinvoice->getCode(),
                'date' => $preinvoice->getDate(),
                'des' => $preinvoice->getDes(),
                'person' => [
                    'code' => $preinvoice->getPerson()->getId(),
                    'nikename' => $preinvoice->getPerson()->getNikename()
                ],
                'amount' => $preinvoice->getAmount(),
                'discountAll' => $preinvoice->getTotalDiscount(),
                'transferCost' => $preinvoice->getShippingCost(),
                'totalAmount' => $totalAmount,
                'label' => $preinvoice->getInvoiceLabel() ? [
                    'code' => $preinvoice->getInvoiceLabel()->getCode(),
                    'label' => $preinvoice->getInvoiceLabel()->getLabel()
                ] : null
            ];
        }

        return new JsonResponse($result);
    }

    #[Route('/api/preinvoice/remove/group', name: 'app_presell_delete_group')]
    public function deletePreinvoiceGroup(Log $log, Access $access, EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $acc = $access->hasRole('preinvoice');
        if (!$acc) {
            return new JsonResponse($this->extractor->operationFail('دسترسی ندارید'), 403);
        }

        $data = json_decode($request->getContent(), true);
        $items = $data['items'] ?? [];

        if (empty($items)) {
            return new JsonResponse(['result' => 2, 'message' => 'هیچ موردی انتخاب نشده است']);
        }

        foreach ($items as $itemCode) {
            $preinvoice = $entityManager->getRepository(PreInvoiceDoc::class)
                ->findOneBy(['code' => $itemCode['code'], 'bid' => $acc['bid'], 'year' => $acc['year']]);
            
            if ($preinvoice) {
                // حذف آیتم‌های پیش فاکتور
                foreach ($preinvoice->getPreInvoiceItems() as $item) {
                    $entityManager->remove($item);
                }

                $entityManager->remove($preinvoice);
            }
            $log->insert(
                'پیش فاکتور',
                'پیش فاکتور شماره ' . $preinvoice->getCode() . ' حذف شد.',
                $this->getUser(),
                $acc['bid'],
            );
        }

        $entityManager->flush();

        return new JsonResponse(['result' => 1, 'message' => 'پیش فاکتورها با موفقیت حذف شدند']);
    }

    #[Route('/api/preinvoice/print/invoice', name: 'app_preinvoice_print_invoice')]
    public function printPreinvoice(Printers $printers, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        $acc = $access->hasRole('preinvoice');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        $preinvoice = $entityManager->getRepository(PreInvoiceDoc::class)->findOneBy([
            'bid' => $acc['bid'],
            'code' => $params['code'],
            'year' => $acc['year']
        ]);
        
        if (!$preinvoice) {
            throw $this->createNotFoundException();
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
                $this->renderView('pdf/printers/preinvoice.html.twig', [
                    'bid' => $acc['bid'],
                    'doc' => $preinvoice,
                    'items' => $preinvoice->getPreInvoiceItems(),
                    'person' => $preinvoice->getPerson(),
                    'printInvoice' => $params['printers'],
                    'discount' => $preinvoice->getTotalDiscount(),
                    'transfer' => $preinvoice->getShippingCost(),
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
                $this->renderView('pdf/posPrinters/justPreinvoice.html.twig', [
                    'bid' => $acc['bid'],
                    'doc' => $preinvoice,
                    'items' => $preinvoice->getPreInvoiceItems(),
                ]),
                false
            );
            $printers->addFile($pid, $acc, "fastPreinvoice");
        }

        return $this->json(['id' => $pdfPid]);
    }
}
