<?php

namespace App\Controller;

use App\Entity\ArchiveFile;
use App\Entity\ArchiveOrders;
use App\Service\Access;
use App\Service\Jdate;
use App\Service\Log;
use App\Service\Notification;
use App\Service\PayMGR;
use App\Service\Provider;
use App\Service\registryMGR; // اضافه کردن سرویس رجیستری
use App\Service\twigFunctions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class ArchiveController extends AbstractController
{
    private function getArchiveInfo(EntityManagerInterface $entityManager, array $acc)
    {
        $orders = $entityManager->getRepository(ArchiveOrders::class)->findBy([
            'bid' => $acc['bid'],
            'status' => 100
        ]);
        $totalSize = 0;
        foreach ($orders as $order) {
            if ($order->getExpireDate() >= time())
                $totalSize += $order->getOrderSize();
        }
        $usedSize = 0;
        $files = $entityManager->getRepository(ArchiveFile::class)->findBy(['bid' => $acc['bid']]);
        foreach ($files as $file)
            $usedSize += $file->getFileSize();
        return [
            'size' => $totalSize * 1024,
            'remain' => ($totalSize * 1024) - $usedSize,
            'used' => $usedSize
        ];
    }

    #[Route('/api/archive/info', name: 'app_archive_info')]
    public function app_archive_info(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $resp = $this->getArchiveInfo($entityManager, $acc);
        return $this->json($resp);
    }

    #[Route('/api/archive/order/settings', name: 'app_archive_order_settings')]
    public function app_archive_order_settings(registryMGR $registryMGR, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $rootSystem = 'system_settings';
        $storagePrice = (int) $registryMGR->get($rootSystem, 'cloud_price_per_gb'); // گرفتن قیمت از رجیستری

        return $this->json([
            'priceBase' => $storagePrice
        ]);
    }

    #[Route('/api/archive/order/submit', name: 'app_archive_order_submit')]
    public function app_archive_order_submit(PayMGR $payMGR, registryMGR $registryMGR, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        $rootSystem = 'system_settings';
        $storagePrice = (int) $registryMGR->get($rootSystem, 'cloud_price_per_gb'); // گرفتن قیمت از رجیستری

        $order = new ArchiveOrders();
        $order->setBid($acc['bid']);
        $order->setSubmitter($this->getUser());
        $order->setDateSubmit(time());
        $order->setPrice($params['space'] * $params['month'] * $storagePrice); // استفاده از قیمت رجیستری
        $order->setDes('خرید سرویس فضای ابری به مقدار ' . $params['space'] . ' گیگابایت به مدت ' . $params['month'] . ' ماه ');
        $order->setOrderSize($params['space']);
        $order->setMonth($params['month']);
        $entityManager->persist($order);
        $entityManager->flush();

        $result = $payMGR->createRequest(
            $order->getPrice(),
            $this->generateUrl('api_archive_buy_verify', ["id" => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'خرید فضای ابری'
        );

        if ($result['Success']) {
            $order->setGatePay($result['gate']);
            $order->setVerifyCode($result['authkey']);
            $entityManager->persist($order);
            $entityManager->flush();
            $log->insert(
                'سرویس فضای ابری',
                'صدور فاکتور سرویس فضای ابری به مقدار ' . $params['space'] . ' گیگابایت به مدت ' . $params['month'] . ' ماه ',
                $this->getUser(),
                $acc['bid']
            );
        }
        return $this->json($result);
    }

    #[Route('/api/archive/buy/verify/{id}', name: 'api_archive_buy_verify')]
    public function api_archive_buy_verify(string $id, PayMGR $payMGR, Notification $notification, Request $request, EntityManagerInterface $entityManager, Log $log): Response
    {
        $req = $entityManager->getRepository(ArchiveOrders::class)->find($id);
        if (!$req)
            throw $this->createNotFoundException('');

        $res = $payMGR->verify($req->getPrice(), $req->getVerifyCode(), $request);
        if ($res['Success'] == false) {
            $log->insert('سرویس فضای ابری', 'پرداخت ناموفق سرویس فضای ابری', $this->getUser(), $req->getBid());
            return $this->render('buy/fail.html.twig', ['results' => $res]);
        } else {
            $req->setStatus(100);
            $req->setRefID($res['refID']);
            $req->setCardPan($res['card_pan']);
            $req->setExpireDate(time() + ($req->getMonth() * 30 * 24 * 60 * 60));
            $entityManager->persist($req);
            $entityManager->flush();
            $log->insert(
                'سرویس فضای ابری',
                'پرداخت موفق فاکتور سرویس فضای ابری',
                $req->getSubmitter(),
                $req->getBid()
            );
            $notification->insert('فاکتور فضای ابری پرداخت شد.', '/acc/sms/panel', $req->getBid(), $req->getSubmitter());
            return $this->render('buy/success.html.twig', ['req' => $req]);
        }
    }

    #[Route('/api/archive/list/{cat}', name: 'app_archive_list')]
    public function app_archive_list(string $cat, Jdate $jdate, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('archiveUpload');
        if (!$acc)
            $acc = $access->hasRole('archiveMod');
        if (!$acc)
            $acc = $access->hasRole('archiveDelete');
        if (!$acc)
            throw $this->createAccessDeniedException();
        if ($cat == 'all')
            $files = $entityManager->getRepository(ArchiveFile::class)->findBy(['bid' => $acc['bid']]);
        else
            $files = $entityManager->getRepository(ArchiveFile::class)->findBy(['bid' => $acc['bid'], 'cat' => $cat]);
        $resp = [];
        foreach ($files as $file) {
            $temp = [];
            $temp['id'] = $file->getId();
            $temp['filename'] = $file->getFilename();
            $temp['fileType'] = $file->getFileType();
            $temp['submitter'] = $file->getSubmitter()->getFullName();
            $temp['dateSubmit'] = $jdate->jdate('Y/n/d H:i', $file->getDateSubmit());
            $temp['filePublicls'] = $file->isPublic();
            $temp['cat'] = $file->getCat();
            $temp['filesize'] = $file->getFileSize();
            $resp[] = $temp;
        }

        return $this->json($resp);
    }

    #[Route('/api/archive/orders/list', name: 'app_archive_orders_list')]
    public function app_archive_orders_list(Jdate $jdate, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $orders = $entityManager->getRepository(ArchiveOrders::class)->findBy([
            'bid' => $acc['bid']
        ], ['id' => 'DESC']);
        $resp = $provider->ArrayEntity2Array($orders, 0);
        foreach ($resp as &$item) {
            $item['dateSubmit'] = $jdate->jdate('Y/n/d H:i', $item['dateSubmit']);
        }
        return $this->json($resp);
    }

    #[Route('/api/archive/file/upload', name: 'app_archive_file_upload')]
    public function app_archive_file_upload(Jdate $jdate, Provider $provider, SluggerInterface $slugger, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('archiveUpload');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $info = $this->getArchiveInfo($entityManager, $acc);
        $uploadedFile = $request->files->get('image');
        if ($uploadedFile) {
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

            try {
                $uploadedFile->move(
                    $this->getParameter('archiveTempMediaDir'),
                    $newFilename
                );
            } catch (FileException $e) {
                return $this->json("error");
            }

            return $this->json(['name' => $newFilename]);
        }
    }

    #[Route('/api/archive/file/save', name: 'app_archive_file_save')]
    public function app_archive_file_save(Jdate $jdate, Provider $provider, SluggerInterface $slugger, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('archiveUpload');
        if (!$acc)
            throw $this->createAccessDeniedException();
        foreach ($request->get('added_media') as $item) {
            if (file_exists(__DIR__ . '/../../../hesabixArchive/temp/' . $item)) {
                $size = ceil(filesize(__DIR__ . '/../../../hesabixArchive/temp/' . $item) / (1024 * 1024));
                $info = $this->getArchiveInfo($entityManager, $acc);
                if ($info['size'] < ($info['used'] + $size))
                    return $this->json(['result' => 'nem']);
                $file = new ArchiveFile();
                $file->setBid($acc['bid']);
                $file->setDateSubmit(time());
                $file->setSubmitter($this->getUser());
                $file->setPublic(false);
                $file->setFilename($item);
                $file->setDes($request->get('des'));
                $file->setCat($request->get('cat'));
                $mimFile = mime_content_type(__DIR__ . '/../../../hesabixArchive/temp/' . $item);
                $file->setFileType($mimFile);
                $file->setFileSize(ceil(filesize(__DIR__ . '/../../../hesabixArchive/temp/' . $item) / (1024 * 1024)));
                rename(__DIR__ . '/../../../hesabixArchive/temp/' . $item, __DIR__ . '/../../../hesabixArchive/' . $item);
                $file->setRelatedDocType($request->get('doctype'));
                $file->setRelatedDocCode($request->get('docid'));
                $entityManager->persist($file);
                $entityManager->flush();
                $log->insert('آرشیو', 'فایل با نام ' . $file->getFilename() . ' افزوده شد.', $this->getUser(), $acc['bid']);
            }
        }
        return $this->json([
            'ok' => 'ok'
        ]);
    }

    #[Route('/api/archive/files/list', name: 'app_archive_file_list')]
    public function app_archive_file_list(Jdate $jdate, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('archiveView');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        $files = $entityManager->getRepository(ArchiveFile::class)->findBy([
            'bid' => $acc['bid'],
            'relatedDocType' => $params['type'],
            'relatedDocCode' => $params['id']
        ]);
        $resp = [];
        foreach ($files as $file) {
            $temp = [];
            $temp['id'] = $file->getId();
            $temp['filename'] = $file->getFilename();
            $temp['fileType'] = $file->getFileType();
            $temp['submitter'] = $file->getSubmitter()->getFullName();
            $temp['dateSubmit'] = $jdate->jdate('Y/n/d H:i', $file->getDateSubmit());
            $temp['filePublicls'] = $file->isPublic();
            $temp['cat'] = $file->getCat();
            $temp['filesize'] = $file->getFileSize();
            $resp[] = $temp;
        }
        return $this->json($resp);
    }

    #[Route('/api/archive/file/get/{id}', name: 'app_archive_file_get')]
    public function app_archive_file_get(string $id, Jdate $jdate, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): BinaryFileResponse
    {
        $acc = $access->hasRole('archiveView');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $file = $entityManager->getRepository(ArchiveFile::class)->find($id);
        if (!$file)
            throw $this->createNotFoundException();
        if ($acc['bid']->getId() != $file->getBid()->getId())
            throw $this->createAccessDeniedException();
        $fileAdr = __DIR__ . '/../../../hesabixArchive/' . $file->getFilename();
        $response = new BinaryFileResponse($fileAdr);
        return $response;
    }

    #[Route('/api/archive/file/remove/{id}', name: 'app_archive_file_remove')]
    public function app_archive_file_remove(string $id, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('archiveDelete');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $file = $entityManager->getRepository(ArchiveFile::class)->find($id);
        if (!$file)
            throw $this->createNotFoundException();
        if ($acc['bid']->getId() != $file->getBid()->getId())
            throw $this->createAccessDeniedException();
        $fileAdr = __DIR__ . '/../../../hesabixArchive/' . $file->getFilename();
        unlink($fileAdr);
        $entityManager->remove($file);
        $entityManager->flush();
        $log->insert('آرشیو', 'فایل با نام ' . $file->getFilename() . ' حذف شد.', $this->getUser(), $acc['bid']);
        return $this->json(['result' => 1]);
    }
}