<?php

namespace App\Controller;

use App\Entity\Plugin;
use App\Entity\PluginProdect;
use App\Service\Access;
use App\Service\Extractor;
use App\Service\Jdate;
use App\Service\Log;
use App\Service\PayMGR;
use App\Service\twigFunctions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use OpenApi\Annotations as OA;

class PluginController extends AbstractController
{
    private const PRICE_MULTIPLIER = 1.09; // ضریب قیمت به صورت ثابت برای محاسبه

    /**
     * بررسی دسترسی کاربر با نقش مشخص
     * 
     * @param Access $access سرویس مدیریت دسترسی
     * @param string $role نقش مورد نیاز
     * @return array اطلاعات دسترسی کاربر
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException در صورت عدم دسترسی
     */
    private function checkAccess(Access $access, string $role): array
    {
        $acc = $access->hasRole($role);
        if (!$acc) {
            throw $this->createAccessDeniedException('شما دسترسی لازم را ندارید.');
        }
        return $acc;
    }

    /**
     * دریافت اطلاعات یک افزونه خاص
     * 
     * @OA\Post(
     *     path="/api/plugin/get/info/{id}",
     *     summary="دریافت اطلاعات افزونه با کد مشخص",
     *     tags={"Plugins"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="کد افزونه",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="اطلاعات افزونه",
     *         @OA\JsonContent(ref="#/components/schemas/PluginProdect")
     *     ),
     *     @OA\Response(response=403, description="دسترسی غیرمجاز"),
     *     @OA\Response(response=404, description="افزونه یافت نشد")
     * )
     */
    #[Route('/api/plugin/get/info/{id}', name: 'api_plugin_get_info', methods: ["POST"])]
    public function api_plugin_get_info(string $id, Access $access, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->checkAccess($access, 'join');
        $item = $entityManager->getRepository(PluginProdect::class)->findOneBy(['code' => $id])
            ?? throw $this->createNotFoundException('افزونه یافت نشد');
        return $this->json($item);
    }

    /**
     * ثبت یک افزونه جدید و ایجاد درخواست پرداخت
     * 
     * @OA\Post(
     *     path="/api/plugin/insert/{id}",
     *     summary="ثبت افزونه جدید و صدور فاکتور پرداخت",
     *     tags={"Plugins"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="شناسه محصول افزونه",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="نتیجه درخواست پرداخت",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=403, description="دسترسی غیرمجاز"),
     *     @OA\Response(response=404, description="افزونه یافت نشد")
     * )
     */
    #[Route('/api/plugin/insert/{id}', name: 'api_plugin_insert', methods: ["POST"])]
    public function api_plugin_insert(string $id, Log $log, twigFunctions $twigFunctions, PayMGR $payMGR, Access $access, EntityManagerInterface $entityManager): Response
    {
        $acc = $this->checkAccess($access, 'join');
        $pp = $entityManager->getRepository(PluginProdect::class)->find($id)
            ?? throw $this->createNotFoundException('افزونه یافت نشد');

        $plugin = new Plugin();
        $plugin->setBid($acc['bid'])
            ->setSubmitter($this->getUser())
            ->setDateSubmit(time())
            ->setStatus(0)
            ->setDes($pp->getName())
            ->setName($pp->getCode())
            ->setPrice($pp->getPrice() * self::PRICE_MULTIPLIER)
            ->setDateExpire(time() + $pp->getTimestamp());

        $entityManager->persist($plugin);

        $result = $payMGR->createRequest(
            $plugin->getPrice(),
            $this->generateUrl('api_plugin_buy_verify', ['id' => $plugin->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'خرید فضای ابری'
        );

        if ($result['Success'] ?? false) {
            $plugin->setGatePay($result['gate'])
                ->setVerifyCode($result['authkey']);
            $log->insert('بازار افزونه‌ها', 'صدور فاکتور افزونه ' . $pp->getName(), $this->getUser(), $acc['bid']);
        }

        $entityManager->flush();
        return $this->json($result);
    }

    /**
     * تأیید پرداخت خرید افزونه
     * 
     * @OA\Post(
     *     path="/api/plugin/buy/verify/{id}",
     *     summary="تأیید پرداخت و فعال‌سازی افزونه",
     *     tags={"Plugins"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="شناسه افزونه",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="پرداخت موفق و فعال‌سازی افزونه"),
     *     @OA\Response(response=400, description="پرداخت ناموفق"),
     *     @OA\Response(response=404, description="افزونه یافت نشد")
     * )
     */
    #[Route('/api/plugin/buy/verify/{id}', name: 'api_plugin_buy_verify', methods: ["POST"])]
    public function api_plugin_buy_verify(string $id, twigFunctions $twigFunctions, PayMGR $payMGR, Request $request, EntityManagerInterface $entityManager, Log $log): Response
    {
        $req = $entityManager->getRepository(Plugin::class)->find($id)
            ?? throw $this->createNotFoundException('درخواست افزونه یافت نشد');

        $res = $payMGR->verify($req->getPrice(), $req->getVerifyCode(), $request);

        if (!($res['Success'] ?? false)) {
            $log->insert(
                'بازار افزونه‌ها ' . $req->getName(),
                'پرداخت ناموفق صورت‌حساب خرید افزونه',
                $req->getSubmitter(),
                $req->getBid()
            );
            return $this->render('buy/fail.html.twig', ['results' => $res]);
        }

        $req->setStatus(100)
            ->setRefID($res['refID'] ?? '')
            ->setCardPan($res['card_pan'] ?? '');
        $entityManager->persist($req);
        $entityManager->flush();
        $log->insert(
            'افزونه ' . $req->getName(),
            'افزونه جدید خریداری و فعال شد.',
            $req->getSubmitter(),
            $req->getBid()
        );
        return $this->render('buy/success.html.twig', ['req' => $req]);
    }

    /**
     * دریافت لیست افزونه‌های فعال
     * 
     * @OA\Post(
     *     path="/api/plugin/get/actives",
     *     summary="دریافت افزونه‌های فعال برای کسب‌وکار",
     *     tags={"Plugins"},
     *     @OA\Response(
     *         response=200,
     *         description="لیست افزونه‌های فعال",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=403, description="دسترسی غیرمجاز")
     * )
     */
    #[Route('/api/plugin/get/actives', name: 'api_plugin_get_actives', methods: ["POST"])]
    public function api_plugin_get_actives(Access $access, Jdate $jdate, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $this->checkAccess($access, 'join');
        $plugins = $entityManager->getRepository(Plugin::class)->findActivePlugins($acc['bid']);
        $temp = [];

        foreach ($plugins as $plugin) {
            $pluginName = $plugin->getName();
            $temp[$pluginName] = $temp[$pluginName] ?? [
                'id' => $plugin->getId(),
                'dateExpire' => $jdate->jdate('Y/n/d', $plugin->getDateExpire()),
                'name' => $pluginName,
                'des' => $plugin->getDes(),
                'dateSubmit' => $plugin->getDateSubmit()
            ];
        }

        $pluginProducts = $entityManager->getRepository(PluginProdect::class)->findBy(['defaultOn' => true]);
        foreach ($pluginProducts as $plugin) {
            $pluginName = $plugin->getCode();
            $temp[$pluginName] = $temp[$pluginName] ?? [
                'id' => $plugin->getId(),
                'dateExpire' => $jdate->jdate('Y/n/d', time() + 96200),
                'name' => $pluginName,
                'des' => $plugin->getName(),
                'dateSubmit' => time(),
            ];
        }

        return $this->json($temp);
    }

    /**
     * دریافت لیست افزونه‌های پرداخت‌شده
     * 
     * @OA\Post(
     *     path="/api/plugin/get/paids",
     *     summary="دریافت افزونه‌های پرداخت‌شده برای کسب‌وکار",
     *     tags={"Plugins"},
     *     @OA\Response(
     *         response=200,
     *         description="لیست افزونه‌های پرداخت‌شده",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Plugin"))
     *     ),
     *     @OA\Response(response=403, description="دسترسی غیرمجاز")
     * )
     */
    #[Route('/api/plugin/get/paids', name: 'api_plugin_get_paids', methods: ["POST"])]
    public function api_plugin_get_paids(Access $access, Jdate $jdate, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $this->checkAccess($access, 'join');
        $plugins = $entityManager->getRepository(Plugin::class)->findBy(['bid' => $acc['bid']]);

        foreach ($plugins as $plugin) {
            $plugin->setDateExpire($jdate->jdate('Y/n/d', $plugin->getDateExpire()));
            $plugin->setDateSubmit($jdate->jdate('Y/n/d', $plugin->getDateSubmit()));
            $plugin->setPrice(number_format($plugin->getPrice()));
        }

        return $this->json($plugins);
    }

    /**
     * دریافت همه افزونه‌های موجود (غیر پیش‌فرض)
     * 
     * @OA\Post(
     *     path="/api/plugin/get/all",
     *     summary="دریافت همه افزونه‌های غیر پیش‌فرض",
     *     tags={"Plugins"},
     *     @OA\Response(
     *         response=200,
     *         description="لیست افزونه‌ها",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/PluginProdect"))
     *     ),
     *     @OA\Response(response=403, description="دسترسی غیرمجاز")
     * )
     */
    #[Route('/api/plugin/get/all', name: 'api_plugin_get_all', methods: ["POST"])]
    public function api_plugin_get_all(Access $access, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->checkAccess($access, 'join');
        $plugins = $entityManager->getRepository(PluginProdect::class)
            ->createQueryBuilder('p')
            ->where('p.defaultOn = :falseValue OR p.defaultOn IS NULL')
            ->setParameter('falseValue', false)
            ->getQuery()
            ->getResult();

        return $this->json($plugins);
    }

    /**
     * دریافت لیست همه افزونه‌ها (برای ادمین)
     * 
     * @OA\Post(
     *     path="/api/admin/plugins/list",
     *     summary="دریافت لیست کامل افزونه‌ها (ادمین)",
     *     tags={"Admin Plugins"},
     *     @OA\Response(
     *         response=200,
     *         description="لیست همه افزونه‌ها",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    #[Route('/api/admin/plugins/list', name: 'api_admin_plugins_get_all', methods: ["POST"])]
    public function api_admin_plugins_get_all(EntityManagerInterface $entityManager): JsonResponse
    {
        $plugins = $entityManager->getRepository(PluginProdect::class)->findAll();
        $res = array_map(fn($plugin) => [
            'id' => $plugin->getId(),
            'name' => $plugin->getName(),
            'price' => $plugin->getPrice(),
            'time' => $plugin->getTimestamp(),
            'defaultOn' => $plugin->isDefaultOn(),
            'timeLabel' => $plugin->getTimelabel(),
        ], $plugins);

        return $this->json($res);
    }

    /**
     * به‌روزرسانی اطلاعات یک افزونه (برای ادمین)
     * 
     * @OA\Post(
     *     path="/api/admin/plugin/update/data",
     *     summary="به‌روزرسانی اطلاعات افزونه (ادمین)",
     *     tags={"Admin Plugins"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="string", description="شناسه افزونه"),
     *             @OA\Property(property="name", type="string", description="نام افزونه"),
     *             @OA\Property(property="price", type="number", description="قیمت"),
     *             @OA\Property(property="time", type="integer", description="مدت زمان"),
     *             @OA\Property(property="timeLabel", type="string", description="برچسب زمان"),
     *             @OA\Property(property="defaultOn", type="boolean", description="وضعیت پیش‌فرض")
     *         )
     *     ),
     *     @OA\Response(response=200, description="به‌روزرسانی موفق"),
     *     @OA\Response(response=400, description="پارامترها ناقص"),
     *     @OA\Response(response=404, description="افزونه یافت نشد")
     * )
     */
    #[Route('/api/admin/plugin/update/data', name: 'api_admin_plugin_update_data', methods: ["POST"])]
    public function api_admin_plugin_update_data(Extractor $extractor, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $params = $request->getPayload()->all();
        if (!isset($params['id'])) {
            return $this->json($extractor->paramsNotSend());
        }

        $plugin = $entityManager->getRepository(PluginProdect::class)->find($params['id'])
            ?? throw $this->createNotFoundException('افزونه یافت نشد');

        $plugin->setPrice($params['price'] ?? $plugin->getPrice())
            ->setName($params['name'] ?? $plugin->getName())
            ->setTimelabel($params['timeLabel'] ?? $plugin->getTimelabel())
            ->setDefaultOn($params['defaultOn'] ?? $plugin->isDefaultOn())
            ->setTimestamp($params['time'] ?? $plugin->getTimestamp());

        $entityManager->persist($plugin);
        $entityManager->flush();

        return $this->json($extractor->operationSuccess());
    }
}