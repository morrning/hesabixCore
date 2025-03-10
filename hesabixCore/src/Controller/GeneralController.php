<?php

namespace App\Controller;

use App\Entity\ChangeReport;
use App\Entity\Statment;
use App\Entity\PrinterQueue;
use App\Service\Jdate;
use App\Service\pdfMGR;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GeneralController extends AbstractController
{
    #[Route('/api/general/stat', name: 'general_stat')]
    public function general_stat(EntityManagerInterface $entityManager, Jdate $jdate): JsonResponse
    {
        $version = '0.0.1';
        $lastUpdateDate = '1399';
        $lastUpdateDes = '';

        $last = $entityManager->getRepository(ChangeReport::class)->findOneBy([], ['id' => 'DESC']);
        if ($last) {
            $version = $last->getVersion();
            $lastUpdateDate = $jdate->jdate('Y/n/d', $last->getDateSubmit());
            $lastUpdateDes = $last->getBody();
        }

        return $this->json([
            'version' => $version,
            'lastUpdateDate' => $lastUpdateDate,
            'lastUpdateDes' => $lastUpdateDes,
        ]);
    }

    #[Route('/api/general/statements', name: 'general_statement')]
    public function general_statement(EntityManagerInterface $entityManager): JsonResponse
    {
        return $this->json($entityManager->getRepository(Statment::class)->findBy([], ['id' => 'DESC']));
    }

    #[Route('/api/general/get/time', name: 'general_get_time')]
    public function general_get_time(Jdate $jdate, Request $request): JsonResponse
    {
        $params = json_decode($request->getContent(), true) ?? [];
        $format = $params['format'] ?? 'Y/n/d';
        return $this->json(['timeNow' => $jdate->jdate($format, time()), 'ts' => time()]);
    }

    #[Route('/front/print/{id}', name: 'app_front_print')]
    public function app_front_print(Provider $provider, EntityManagerInterface $entityManager, PdfMGR $pdfMGR, string $id): Response
    {
        
        $print = $entityManager->getRepository(PrinterQueue::class)->findOneBy(['pid' => $id]);

        if (!$print) {
            return new JsonResponse(['error' => 'Print job not found'], Response::HTTP_NOT_FOUND);
        }

        if (empty($print->getView())) {
            return new JsonResponse(['error' => 'Empty print content'], Response::HTTP_BAD_REQUEST);
        }

        // تولید PDF و گرفتن محتوا به صورت رشته
        if ($print->isPosprint()) {
            $pdfContent = $pdfMGR->generateTwig2PDFInvoiceType($print); // متد جدید برای گرفتن محتوا
        } else {
            $pdfContent = $pdfMGR->generateTwig2PDF($print); // متد جدید برای گرفتن محتوا
        }

        // برگرداندن PDF به عنوان پاسخ
        return new Response(
            $pdfContent,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="Hesabix PrintOut.pdf"',
            ]
        );
    }
}
