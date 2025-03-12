<?php

namespace App\Controller;

use App\Service\Access;
use App\Service\Log;
use App\Service\Notification;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\AccountingPackageOrder;
use App\Service\PayMGR;
use App\Service\registryMGR;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AccountingPackageController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private PayMGR $payMGR;
    private registryMGR $registryMGR;

    public function __construct(EntityManagerInterface $entityManager, PayMGR $payMGR, registryMGR $registryMGR)
    {
        $this->entityManager = $entityManager;
        $this->payMGR = $payMGR;
        $this->registryMGR = $registryMGR;
    }

    #[Route('/api/packagemanager/packages/list', name: 'list_accounting_packages', methods: ['POST'])]
    public function listPackages(Access $access): JsonResponse
    {
        $acc = $access->hasRole('owner');
        if (!$acc) {
            return $this->json(['result' => false, 'message' => 'به این بخش دسترسی ندارید'], 400);
        }
        $basePrice = (int) $this->registryMGR->get('system_settings', 'unlimited_price') ?: 500000;
        $selectedDurations = json_decode($this->registryMGR->get('system_settings', 'unlimited_duration') ?: '[]', true);

        $packages = array_map(function ($month) use ($basePrice) {
            return [
                'month' => (int) $month,
                'price' => (string) ((int) $month * $basePrice),
            ];
        }, $selectedDurations);

        // مرتب‌سازی بر اساس ماه
        usort($packages, fn($a, $b) => $a['month'] <=> $b['month']);

        return $this->json(['packages' => $packages]);
    }

    #[Route('/api/packagemanager/package/order/new', name: 'new_accounting_package_order', methods: ['POST'])]
    public function newPackageOrder(Access $access, Request $request): JsonResponse
    {
        $acc = $access->hasRole('owner');
        $data = json_decode($request->getContent(), true);
        $month = $data['month'] ?? null;

        if (!$month || !$acc) {
            return $this->json(['result' => false, 'message' => 'اطلاعات ناقص است.'], 400);
        }

        $selectedDurations = json_decode($this->registryMGR->get('system_settings', 'unlimited_duration') ?: '[]', true);
        if (!in_array((string) $month, $selectedDurations)) {
            return $this->json(['result' => false, 'message' => 'مدت زمان انتخاب‌شده مجاز نیست.'], 400);
        }


        $basePrice = (int) $this->registryMGR->get('system_settings', 'unlimited_price') ?: 500000;
        $price = (string) ($month * $basePrice);

        $order = new AccountingPackageOrder();
        $order->setBid($acc['bid']);
        $order->setDateSubmit((string) time());
        $order->setDateExpire((string) (time() + $month * 30 * 24 * 60 * 60));
        $order->setMonth($month);
        $order->setPrice($price);
        $order->setSubmitter($this->getUser());
        $order->setStatus(0);
        $order->setDes("سفارش بسته حسابداری نامحدود $month ماهه");
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        $callbackUrl = $this->generateUrl('api_packagemanager_buy_verify', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $paymentResult = $this->payMGR->createRequest($price, $callbackUrl, $order->getDes(), $order->getId());
        $order->setGatePay($paymentResult['gate']);
        $order->setVerifyCode($paymentResult['authkey']);
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        if ($paymentResult['Success']) {
            $order->setGatePay($paymentResult['gate']);
            $this->entityManager->persist($order);
            $this->entityManager->flush();

            return $this->json([
                'result' => true,
                'paymentUrl' => $paymentResult['targetURL'],
                'message' => 'سفارش ثبت شد. به صفحه پرداخت هدایت می‌شوید.',
            ]);
        }

        return $this->json([
            'result' => false,
            'message' => 'خطا در اتصال به درگاه پرداخت.',
        ], 500);
    }

    #[Route('/api/packagemanager/package/buy/verify/{id}', name: 'api_packagemanager_buy_verify')]
    public function api_packagemanager_buy_verify(string $id, PayMGR $payMGR, Notification $notification, Request $request, EntityManagerInterface $entityManager, Log $log): Response
    {
        $req = $entityManager->getRepository(AccountingPackageOrder::class)->find($id);
        if (!$req)
            throw $this->createNotFoundException('');

        $res = $payMGR->verify($req->getPrice(), $req->getVerifyCode(), $request);
        if ($res['Success'] == false) {
            $log->insert('بسته حسابداری نامحدود', 'پرداخت ناموفق بسته حسابداری نامحدود', $this->getUser(), $req->getBid());
            return $this->render('buy/fail.html.twig', ['results' => $res]);
        } else {
            $req->setStatus(100);
            $req->setRefID($res['refID']);
            $req->setCardPan($res['card_pan']);
            $entityManager->persist($req);
            $entityManager->flush();
            $log->insert(
                'بسته نامحدود حسابداری',
                'پرداخت موفق فاکتور بسته نامحدود حسابداری',
                $req->getSubmitter(),
                $req->getBid()
            );
            $notification->insert('فاکتور بسته حسابداری نامحدود پرداخت شد.', '/acc/package/order/list', $req->getBid(), $req->getSubmitter());
            return $this->render('buy/success.html.twig', ['req' => $req]);
        }
    }

    #[Route('/api/packagemanager/packages/orders/list', name: 'list_accounting_package_orders', methods: ['POST'])]
    public function listPackageOrders(Access $access): JsonResponse
    {
        $acc = $access->hasRole('owner');
        if (!$acc) {
            return $this->json(['result' => false, 'message' => 'به این بخش دسترسی ندارید'], 403);
        }

        $repository = $this->entityManager->getRepository(AccountingPackageOrder::class);
        $orders = $repository->findBy(['bid' => $acc['bid']], ['dateSubmit' => 'DESC']);

        $ordersData = array_map(function (AccountingPackageOrder $order) {
            return [
                'id' => $order->getId(),
                'dateSubmit' => $order->getDateSubmit(),
                'dateExpire' => $order->getDateExpire(),
                'month' => $order->getMonth(),
                'price' => $order->getPrice(),
                'status' => $order->getStatus(),
                'des' => $order->getDes(),
            ];
        }, $orders);

        return $this->json([
            'result' => true,
            'orders' => $ordersData
        ]);
    }
}