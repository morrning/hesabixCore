<?php

namespace App\Controller;

use App\Entity\Settings;
use App\Entity\Support;
use App\Entity\User;
use App\Service\Access;
use App\Service\Explore;
use App\Service\Extractor;
use App\Service\Jdate;
use App\Service\Notification;
use App\Service\Provider;
use App\Service\registryMGR;
use App\Service\SMS;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SupportController extends AbstractController
{
    // ثابت‌ها برای مدیریت خطاها
    private const ERROR_TICKET_NOT_FOUND = ['error' => 1, 'message' => 'تیکت یافت نشد.'];
    private const ERROR_NO_FILE = ['error' => 2, 'message' => 'فایلی برای این تیکت وجود ندارد.'];
    private const ERROR_FILE_NOT_FOUND = ['error' => 3, 'message' => 'فایل در سرور یافت نشد.'];
    private const ERROR_ACCESS_DENIED = ['error' => 4, 'message' => 'شما اجازه دسترسی به این فایل را ندارید.'];
    private const ERROR_INVALID_PARAMS = ['error' => 999, 'message' => 'تمام موارد لازم را وارد کنید.'];

    /**
     * Generate a random string
     */
    private function randomString(int $length = 32): string
    {
        return substr(str_shuffle(str_repeat('23456789ABCDEFGHJKLMNPQRSTUVWXYZ', ceil($length / 32))), 1, $length);
    }

    /**
     * Fetch ticket by ID and check ownership
     */
    private function getTicket(EntityManagerInterface $entityManager, string $id, bool $checkOwnership = false): ?Support
    {
        $ticket = $entityManager->getRepository(Support::class)->find($id);
        if (!$ticket || ($checkOwnership && $ticket->getSubmitter() !== $this->getUser())) {
            return null;
        }
        return $ticket;
    }

    #[Route('/api/admin/support/list', name: 'app_admin_support_list')]
    public function app_admin_support_list(Extractor $extractor, EntityManagerInterface $entityManager): JsonResponse
    {
        $items = $entityManager->getRepository(Support::class)->findBy(['main' => 0], ['id' => 'DESC']);
        $res = array_map(fn($item) => Explore::ExploreSupportTicket($item, $this->getUser()), $items);
        return $this->json($extractor->operationSuccess($res));
    }

    #[Route('/api/admin/support/view/{id}', name: 'app_admin_support_view')]
    public function app_admin_support_view(Extractor $extractor, EntityManagerInterface $entityManager, string $id): JsonResponse
    {
        $item = $this->getTicket($entityManager, $id);
        if (!$item) {
            throw $this->createNotFoundException();
        }

        $replays = $entityManager->getRepository(Support::class)->findBy(['main' => $item->getId()]);
        $res = array_map(function ($replay) {
            $replay->setState($replay->getSubmitter() === $this->getUser() ? 1 : 0);
            return Explore::ExploreSupportTicket($replay, $this->getUser());
        }, $replays);

        return $this->json($extractor->operationSuccess([
            'item' => Explore::ExploreSupportTicket($item, $this->getUser()),
            'replays' => $res
        ]));
    }

    #[Route('/api/admin/support/mod/{id}', name: 'app_admin_support_mod')]
    public function app_admin_support_mod(registryMGR $registryMGR, SMS $SMS, Request $request, EntityManagerInterface $entityManager, Notification $notifi, string $id): JsonResponse
    {
        $params = $request->getPayload()->all();
        $item = $this->getTicket($entityManager, $id);
        if (!$item) {
            throw $this->createNotFoundException();
        }

        if (!isset($params['body'])) {
            return $this->json(self::ERROR_INVALID_PARAMS);
        }

        $support = new Support();
        $support->setDateSubmit(time())
            ->setTitle('0')
            ->setBody($params['body'])
            ->setState('0')
            ->setMain($item->getId())
            ->setSubmitter($this->getUser());

        // ذخیره موقت برای گرفتن ID
        $entityManager->persist($support);
        $entityManager->flush();

        // مدیریت فایل با متد handleFileUpload
        $fileName = $this->handleFileUpload($request, $this->getParameter('SupportFilesDir'), $support->getId());
        if ($fileName) {
            $support->setFileName($fileName);
        }

        $entityManager->persist($support);
        $item->setState('پاسخ داده شده');
        $entityManager->persist($item);
        $entityManager->flush();

        // بررسی سوئیچ ارسال SMS
        $sendSms = filter_var($params['sendSms'] ?? true, FILTER_VALIDATE_BOOLEAN); // پیش‌فرض true
        if ($sendSms && ($mobile = $item->getSubmitter()->getMobile())) {
            $SMS->send([$item->getId()], $registryMGR->get('sms', 'ticketReplay'), $mobile);
        }

        $settings = $entityManager->getRepository(Settings::class)->findAll()[0];
        $notifi->insert("به درخواست پشتیبانی پاسخ داده شد", "/profile/support-view/{$item->getId()}", null, $item->getSubmitter());

        return $this->json([
            'error' => 0,
            'message' => 'successful',
            'file' => $fileName
        ]);
    }

    /**
     * Handle file upload and return filename
     */
    private function handleFileUpload(Request $request, string $uploadDirectory, int $ticketId): ?string
    {
        if (!file_exists($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }

        $files = $request->files->get('files');
        if ($files && !empty($files)) {
            $file = $files[0]; // فقط اولین فایل
            $extension = $file->getClientOriginalExtension();
            $fileName = $ticketId . '.' . $extension;
            $file->move($uploadDirectory, $fileName);
            return $fileName;
        }
        return null;
    }

    #[Route('/api/support/list', name: 'app_support_list')]
    public function app_support_list(Jdate $jdate, EntityManagerInterface $entityManager): JsonResponse
    {
        $items = $entityManager->getRepository(Support::class)->findBy(
            ['submitter' => $this->getUser(), 'main' => 0],
            ['id' => 'DESC']
        );

        foreach ($items as $item) {
            $item->setDateSubmit($jdate->jdate('Y/n/d', $item->getDateSubmit()));
        }

        return $this->json($items);
    }

    #[Route('/api/support/mod/{id}', name: 'app_support_mod')]
    public function app_support_mod(registryMGR $registryMGR, SMS $SMS, Request $request, EntityManagerInterface $entityManager, string $id = ''): JsonResponse
    {
        $params = $request->getPayload()->all();
        $uploadDirectory = $this->getParameter('SupportFilesDir');
        if (!file_exists($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }

        if ($id === '') {
            if (!isset($params['title'], $params['body'])) {
                return $this->json(self::ERROR_INVALID_PARAMS);
            }

            $item = new Support();
            $item->setBody($params['body'])
                ->setTitle($params['title'])
                ->setDateSubmit(time())
                ->setSubmitter($this->getUser())
                ->setMain(0)
                ->setCode($this->randomString(8))
                ->setState('در حال پیگیری');

            $entityManager->persist($item);
            $entityManager->flush();

            $fileName = $this->handleFileUpload($request, $uploadDirectory, $item->getId());
            if ($fileName) {
                $item->setFileName($fileName);
            }

            $entityManager->persist($item);
            $entityManager->flush();

            $SMS->send([$item->getId()], $registryMGR->get('sms', 'ticketRec'), $registryMGR->get('ticket', 'managerMobile'));

            return $this->json([
                'error' => 0,
                'message' => 'ok',
                'url' => $item->getId(),
                'files' => $fileName
            ]);
        }

        if (!isset($params['body'])) {
            return $this->json(self::ERROR_INVALID_PARAMS);
        }

        $upper = $this->getTicket($entityManager, $id);
        if (!$upper) {
            return $this->json(self::ERROR_TICKET_NOT_FOUND);
        }

        $item = new Support();
        $item->setMain($upper->getId())
            ->setBody($params['body'])
            ->setTitle($upper->getTitle())
            ->setDateSubmit(time())
            ->setSubmitter($this->getUser())
            ->setState('در حال پیگیری');

        $entityManager->persist($item);
        $entityManager->flush();

        $fileName = $this->handleFileUpload($request, $uploadDirectory, $item->getId());
        if ($fileName) {
            $item->setFileName($fileName);
        }

        $entityManager->persist($item);
        $upper->setState('در حال پیگیری');
        $entityManager->persist($upper);
        $entityManager->flush();

        $SMS->send([$item->getId()], $registryMGR->get('sms', 'ticketRec'), $registryMGR->get('ticket', 'managerMobile'));

        return $this->json([
            'error' => 0,
            'message' => 'ok',
            'url' => $item->getId(),
            'files' => $fileName
        ]);
    }

    #[Route('/api/support/view/{id}', name: 'app_support_view')]
    public function app_support_view(EntityManagerInterface $entityManager, string $id): JsonResponse
    {
        $item = $this->getTicket($entityManager, $id, true);
        if (!$item) {
            throw $this->createAccessDeniedException();
        }

        $replays = $entityManager->getRepository(Support::class)->findBy(['main' => $item->getId()]);
        $replaysArray = array_map(fn($replay) => Explore::ExploreSupportTicket($replay, $this->getUser()), $replays);

        return $this->json([
            'item' => Explore::ExploreSupportTicket($item, $this->getUser()),
            'replays' => $replaysArray
        ]);
    }

    #[Route('/api/support/download/file/{id}', name: 'app_support_download_file')]
    public function app_support_download_file(EntityManagerInterface $entityManager, string $id): Response
    {
        $ticket = $this->getTicket($entityManager, $id);
        if (!$ticket) {
            return $this->json(self::ERROR_TICKET_NOT_FOUND, 404);
        }

        $currentUser = $this->getUser();
        if (!$currentUser || (!$this->isGranted('ROLE_ADMIN') && $ticket->getSubmitter() !== $currentUser)) {
            return $this->json(self::ERROR_ACCESS_DENIED, 403);
        }

        $fileName = $ticket->getFileName();
        if (!$fileName) {
            return $this->json(self::ERROR_NO_FILE, 404);
        }

        $filePath = $this->getParameter('SupportFilesDir') . '/' . $fileName;
        if (!file_exists($filePath)) {
            return $this->json(self::ERROR_FILE_NOT_FOUND, 404);
        }

        return new BinaryFileResponse($filePath, 200, [
            'Content-Disposition' => ResponseHeaderBag::DISPOSITION_ATTACHMENT . '; filename="' . $fileName . '"'
        ]);
    }
}