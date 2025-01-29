<?php

namespace App\Controller;

use App\Entity\Printer;
use App\Entity\PrinterQueue;
use App\Entity\PrintItem;
use App\Entity\PrintOptions;
use App\Service\Access;
use App\Service\Explore;
use App\Service\Extractor;
use App\Service\Jdate;
use App\Service\Log;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function PHPUnit\Framework\isNull;

class PrintersController extends AbstractController
{

    /**
     * function to generate random strings
     * @param 		int 	$length 	number of characters in the generated string
     * @return 		string	a new string is created with random characters of the desired length
     */
    private function RandomString($length = 32)
    {
        return substr(str_shuffle(str_repeat($x = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }

    #[Route('/api/printers/options/info', name: 'app_printers_options_info')]
    public function app_printers_options_info(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $settings = $entityManager->getRepository(PrintOptions::class)->findOneBy(['bid' => $acc['bid']]);
        if (!$settings) {
            $settings = new PrintOptions;
            $settings->setBid($acc['bid']);
            $entityManager->persist($settings);
            $entityManager->flush();
        }

        $temp = [];
        $temp['sell']['id'] = $settings->getId();
        $temp['sell']['bidInfo'] = $settings->isSellBidInfo();
        $temp['sell']['taxInfo'] = $settings->isSellTaxInfo();
        $temp['sell']['discountInfo'] = $settings->isSellDiscountInfo();
        $temp['sell']['note'] = $settings->isSellNote();
        $temp['sell']['noteString'] = $settings->getSellNoteString();
        $temp['sell']['pays'] = $settings->isSellPays();
        $temp['sell']['paper'] = $settings->getSellPaper();
        if (!$temp['sell']['paper']) {
            $temp['sell']['paper'] = 'A4-L';
        }

        $temp['buy']['id'] = $settings->getId();
        $temp['buy']['bidInfo'] = $settings->isBuyBidInfo();
        $temp['buy']['taxInfo'] = $settings->isBuyTaxInfo();
        $temp['buy']['discountInfo'] = $settings->isBuyDiscountInfo();
        $temp['buy']['note'] = $settings->isBuyNote();
        $temp['buy']['noteString'] = $settings->getBuyNoteString();
        $temp['buy']['pays'] = $settings->isBuyPays();
        $temp['buy']['paper'] = $settings->getBuyPaper();
        if (!$temp['buy']['paper']) {
            $temp['buy']['paper'] = 'A4-L';
        }

        $temp['rfbuy']['id'] = $settings->getId();
        $temp['rfbuy']['bidInfo'] = $settings->isRfbuyBidInfo();
        $temp['rfbuy']['taxInfo'] = $settings->isRfbuyTaxInfo();
        $temp['rfbuy']['discountInfo'] = $settings->isRfbuyDiscountInfo();
        $temp['rfbuy']['note'] = $settings->isRfbuyNote();
        $temp['rfbuy']['noteString'] = $settings->getRfbuyNoteString();
        $temp['rfbuy']['pays'] = $settings->isRfbuyPays();
        $temp['rfbuy']['paper'] = $settings->getRfbuyPaper();
        if (!$temp['rfbuy']['paper']) {
            $temp['rfbuy']['paper'] = 'A4-L';
        }

        $temp['rfsell']['id'] = $settings->getId();
        $temp['rfsell']['bidInfo'] = $settings->isRfsellBidInfo();
        $temp['rfsell']['taxInfo'] = $settings->isRfsellTaxInfo();
        $temp['rfsell']['discountInfo'] = $settings->isRfsellDiscountInfo();
        $temp['rfsell']['note'] = $settings->isRfsellNote();
        $temp['rfsell']['noteString'] = $settings->getRfsellNoteString();
        $temp['rfsell']['pays'] = $settings->isRfsellPays();
        $temp['rfsell']['paper'] = $settings->getRfsellPaper();

        $temp['fastsell']['cashdeskTicket'] = $settings->isFastsellCashdeskTicket();
        $temp['fastsell']['invoice'] = $settings->isFastsellInvoice();
        $temp['fastsell']['pdf'] = $settings->isFastsellPdf();

        $temp['repservice']['noteString'] = $settings->getRepserviceNoteString();
        $temp['repservice']['paper'] = $settings->getRepServicePaper();
        if (!$temp['rfsell']['paper']) {
            $temp['rfsell']['paper'] = 'A4-L';
        }

        return $this->json($temp);
    }

    #[Route('/api/printers/options/save', name: 'app_printers_options_save')]
    public function app_printers_options_save(Extractor $extractor, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('settings');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $settings = $entityManager->getRepository(PrintOptions::class)->findOneBy(['bid' => $acc['bid']]);
        if (!$settings) {
            $settings = new PrintOptions;
            $settings->setBid($acc['bid']);
            $entityManager->persist($settings);
            $entityManager->flush();
        }
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        $settings->setSellBidInfo($params['sell']['bidInfo']);
        $settings->setSellTaxInfo($params['sell']['taxInfo']);
        $settings->setSellDiscountInfo($params['sell']['discountInfo']);
        $settings->setSellNote($params['sell']['note']);
        $settings->setSellNoteString($params['sell']['noteString']);
        $settings->setSellPays($params['sell']['pays']);
        $settings->setSellPaper($params['sell']['paper']);
        if ($params['buy']['bidInfo'] == null) {
            $settings->setBuyBidInfo(false);
        } else {
            $settings->setBuyBidInfo(true);
        }

        $settings->setBuyTaxInfo($params['buy']['taxInfo']);
        $settings->setBuyDiscountInfo($params['buy']['discountInfo']);
        $settings->setBuyNote($params['buy']['note']);
        $settings->setBuyNoteString($params['buy']['noteString']);
        $settings->setBuyPays($params['buy']['pays']);
        $settings->setBuyPaper($params['buy']['paper']);

        $settings->setRfbuyBidInfo($params['rfbuy']['bidInfo']);
        $settings->setRfbuyTaxInfo($params['rfbuy']['taxInfo']);
        $settings->setRfbuyDiscountInfo($params['rfbuy']['discountInfo']);
        $settings->setRfbuyNote($params['rfbuy']['note']);
        $settings->setRfbuyNoteString($params['rfbuy']['noteString']);
        $settings->setRfbuyPays($params['rfbuy']['pays']);
        $settings->setRfbuyPaper($params['rfbuy']['paper']);

        $settings->setRfsellBidInfo($params['rfsell']['bidInfo']);
        $settings->setRfsellTaxInfo($params['rfsell']['taxInfo']);
        $settings->setRfsellDiscountInfo($params['rfsell']['discountInfo']);
        $settings->setRfsellNote($params['rfsell']['note']);
        $settings->setRfsellNoteString($params['rfsell']['noteString']);
        $settings->setRfsellPays($params['rfsell']['pays']);
        $settings->setRfSellPaper($params['rfsell']['paper']);

        $settings->setRepserviceNoteString($params['repservice']['noteString']);
        $settings->setRepServicePaper($params['repservice']['paper']);

        $settings->setFastsellCashdeskTicket($params['fastsell']['cashdeskTicket']);
        $settings->setFastsellInvoice($params['fastsell']['invoice']);
        $settings->setFastsellPdf($params['fastsell']['pdf']);

        $entityManager->persist($settings);
        $entityManager->flush();
        $log->insert('تنظیمات چاپ', 'تنظیمات چاپ به روز رسانی شد.', $this->getUser(), $acc['bid']->getId());
        return $this->json($extractor->operationSuccess());
    }
    #[Route('/api/printers/list', name: 'app_printers_list')]
    public function app_printers_list(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('owner');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $items = $entityManager->getRepository(Printer::class)->findBy(['bid' => $acc['bid']]);
        $res = [];
        foreach ($items as $item) {
            $temp = [];
            $temp['id'] = $item->getId();
            $temp['name'] = $item->getName();
            $temp['token'] = $item->getToken();
            $temp['status'] = 'معتبر';
            $res[] = $temp;
        }
        return $this->json($res);
    }
    #[Route('/api/printers/delete/{code}', name: 'app_printers_delete')]
    public function app_printers_delete(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('owner');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $printer = $entityManager->getRepository(Printer::class)->findOneBy(['bid' => $acc['bid'], 'id' => $code]);
        if (!$printer)
            throw $this->createNotFoundException();
        //check accounting docs

        $comName = $printer->getName();
        $entityManager->remove($printer);
        $log->insert('چاپگر‌های ابری', '  چاپگر  با نام ' . $comName . ' حذف شد. ', $this->getUser(), $acc['bid']->getId());
        return $this->json(['result' => 1]);
    }

    #[Route('/api/printers/insert', name: 'app_printers_insert')]
    public function app_printers_insert(Extractor $extractor, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('owner');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('name', $params))
            return $this->json($extractor->paramsNotSend());
        $item = $entityManager->getRepository(Printer::class)->findOneBy(['bid' => $acc['bid'], 'name' => $params['name']]);
        if ($item)
            return $this->json(['result' => 2]);
        $item = new Printer();
        $item->setBid($acc['bid']);
        $item->setName($params['name']);
        $item->setToken($this->RandomString(42));
        $entityManager->persist($item);
        $entityManager->flush();
        return $this->json([
            'result' => 1
        ]);
    }

    #[Route('/api/print/last', name: 'app_print_last')]
    public function app_print_last(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): Response
    {
        $acc = $access->hasRole('owner');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $printer = $entityManager->getRepository(Printer::class)->findBy([
            'bid' => $acc['bid'],
            'token' => $request->headers->get('printer-key')
        ]);
        $items = $entityManager->getRepository(PrintItem::class)->findBy([
            'printer' => $printer,
            'printed' => false
        ]);
        if (count($items) == 0)
            return new Response('');
        $items[count($items) - 1]->setPrinted(true);
        $entityManager->persist($items[count($items) - 1]);
        $entityManager->flush();
        return new Response($items[count($items) - 1]->getType() . ',' . $items[count($items) - 1]->getFile());
    }

    #[Route('/api/printers/exist/{code}', name: 'app_printers_exist')]
    public function app_printers_exist(Extractor $extractor, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $printer = $entityManager->getRepository(Printer::class)->findOneBy(['bid' => $acc['bid'], 'id' => $code]);
        if ($printer)
            return $this->json($extractor->operationSuccess());
        return $this->json($extractor->operationFail());
    }

    #[Route('/api/printers/queue/{code}', name: 'app_printers_queue')]
    public function app_printers_queue(Jdate $jdate, Extractor $extractor, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $queue = $entityManager->getRepository(PrinterQueue::class)->findBy(['bid' => $acc['bid']]);
        $resp = [];
        foreach ($queue as $item) {
            $resp[] = [
                'id' => $item->getId(),
                'submitter' => $item->getSubmitter()->getFullName(),
                'code' => $item->getPid(),
                'dateSubmit' => $jdate->jdate('Y/n/d H:i', $item->getDateSubmit())
            ];
        }
        return $this->json($extractor->operationSuccess($resp));
    }

    #[Route('/api/printers/queue_doc_delete/{code}', name: 'app_printers_queuequeue_doc_delete')]
    public function app_printers_queuequeue_doc_delete(Jdate $jdate, Extractor $extractor, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $item = $entityManager->getRepository(PrinterQueue::class)->findOneBy(['bid' => $acc['bid'], 'pid' => $code]);
        if (!$item)
            throw $this->createNotFoundException();
        $log->insert('تنظیمات چاپ', ' برگه پرینت ابری با کد' . $item->getId() . ' حذف شد.', $this->getUser(), $acc['bid']->getId());
        $entityManager->remove($item);
        $entityManager->flush();
        return $this->json($extractor->operationSuccess());
    }

    #[Route('/api/printers/queue_doc_delete_all', name: 'app_printers_queuequeue_doc_delete_all')]
    public function app_printers_queuequeue_doc_delete_all(Jdate $jdate, Extractor $extractor, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $items = $entityManager->getRepository(PrinterQueue::class)->findBy(['bid' => $acc['bid']]);
        foreach ($items as $item) {
            $entityManager->remove($item);
        }
        $log->insert('تنظیمات چاپ', 'لیست چاپگرهای ابری پاک شد.', $this->getUser(), $acc['bid']->getId());
        return $this->json($extractor->operationSuccess());
    }
}
