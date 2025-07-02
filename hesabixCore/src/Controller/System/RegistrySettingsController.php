<?php

namespace App\Controller\System;

use App\Service\registryMGR;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrySettingsController extends AbstractController
{
    private registryMGR $registryMGR;

    public function __construct(registryMGR $registryMGR)
    {
        $this->registryMGR = $registryMGR;
    }

    #[Route('/api/settings/get/can-free-accounting', name: 'get_can_free_accounting', methods: ['POST'])]
    public function getCanFreeAccounting(Request $request): JsonResponse
    {
        try {
            $value = $this->registryMGR->get('system_settings', 'can_free_accounting');
            $responseValue = ($value === '1' || $value === 1) ? 1 : 0;
            return $this->json(['value' => $responseValue]);
        } catch (\Exception $e) {
            return $this->json([
                'value' => 0,
                'error' => 'خطا در بررسی تنظیمات: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/admin/registry/settings/load', name: 'app_registry_settings_load', methods: ['POST'])]
    public function app_registry_settings_load(EntityManagerInterface $entityManagerInterface, registryMGR $registryMGR): JsonResponse
    {
        $rootSystem = 'system_settings';
        $rootTicket = 'ticket';

        $settings = [
            'canRegister' => filter_var($registryMGR->get($rootSystem, 'can_register'), FILTER_VALIDATE_BOOLEAN),
            'canFreeAccounting' => filter_var($registryMGR->get($rootSystem, 'can_free_accounting'), FILTER_VALIDATE_BOOLEAN),
            'smsPrice' => (int) $registryMGR->get($rootSystem, 'sms_price'),
            'cloudPricePerGb' => (int) $registryMGR->get($rootSystem, 'cloud_price_per_gb'),
            'unlimitedPrice' => (int) $registryMGR->get($rootSystem, 'unlimited_price'),
            'accountingDocPrice' => (int) $registryMGR->get($rootSystem, 'accounting_doc_price'),
            'giftCredit' => (int) $registryMGR->get($rootSystem, 'gift_credit', 0), // مقدار پیش‌فرض 0
            'unlimitedDuration' => json_decode($registryMGR->get($rootSystem, 'unlimited_duration') ?: '[]', true),
            'smsAlertEnabled' => filter_var($registryMGR->get($rootSystem, 'sms_alert_enabled'), FILTER_VALIDATE_BOOLEAN),
            'smsAlertMobile' => $registryMGR->get($rootTicket, 'managerMobile'),
            'sponsorMessage' => $registryMGR->get('system', 'sponsers'),
            'footerLeft' => $registryMGR->get('system', 'footerLeft'),
            'footerRight' => $registryMGR->get('system', 'footerRight'),
            'appName' => $registryMGR->get('system', 'appName'),
            'appUrl' => $registryMGR->get('system', 'appUrl'),
            'appSlogan' => $registryMGR->get('system', 'appSlogan'),
            'verifyMobileViaSms' => filter_var($registryMGR->get('system', 'verifyMobileViaSms'), FILTER_VALIDATE_BOOLEAN),
            // تنظیمات FTP
            'ftpEnabled' => filter_var($registryMGR->get($rootSystem, 'ftp_enabled'), FILTER_VALIDATE_BOOLEAN),
            'ftpHost' => $registryMGR->get($rootSystem, 'ftp_host') ?: '',
            'ftpPort' => $registryMGR->get($rootSystem, 'ftp_port') ?: '21',
            'ftpUsername' => $registryMGR->get($rootSystem, 'ftp_username') ?: '',
            'ftpPassword' => $registryMGR->get($rootSystem, 'ftp_password') ?: '',
            'ftpPath' => $registryMGR->get($rootSystem, 'ftp_path') ?: '',
        ];

        return new JsonResponse([
            'result' => 1,
            'data' => $settings
        ]);
    }

    #[Route('/api/admin/registry/settings/save', name: 'app_registry_settings_save', methods: ['POST'])]
    public function app_registry_settings_save(Request $request, EntityManagerInterface $entityManagerInterface, registryMGR $registryMGR): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $rootSystem = 'system_settings';
        $rootTicket = 'ticket';

        $registryMGR->update($rootSystem, 'can_register', $data['canRegister'] ? '1' : '0');
        $registryMGR->update($rootSystem, 'can_free_accounting', $data['canFreeAccounting'] ? '1' : '0');
        $registryMGR->update($rootSystem, 'sms_price', (string) $data['smsPrice']);
        $registryMGR->update($rootSystem, 'cloud_price_per_gb', (string) $data['cloudPricePerGb']);
        $registryMGR->update($rootSystem, 'unlimited_price', (string) $data['unlimitedPrice']);
        $registryMGR->update($rootSystem, 'accounting_doc_price', (string) $data['accountingDocPrice']);
        $registryMGR->update($rootSystem, 'gift_credit', (string) $data['giftCredit']); // ذخیره فیلد جدید
        $registryMGR->update($rootSystem, 'unlimited_duration', json_encode($data['unlimitedDuration']));
        $registryMGR->update($rootSystem, 'sms_alert_enabled', $data['smsAlertEnabled'] ? '1' : '0');
        $registryMGR->update($rootTicket, 'managerMobile', $data['smsAlertMobile'] ?? '');
        $registryMGR->update('system', 'sponsers', $data['sponsorMessage'] ?? '');
        $registryMGR->update('system', 'footerLeft', $data['footerLeft'] ?? '');
        $registryMGR->update('system', 'footerRight', $data['footerRight'] ?? '');
        $registryMGR->update('system', 'appName', $data['appName'] ?? '');
        $registryMGR->update('system', 'appUrl', $data['appUrl'] ?? '');
        $registryMGR->update('system', 'appSlogan', $data['appSlogan'] ?? '');
        $registryMGR->update('system', 'verifyMobileViaSms', $data['verifyMobileViaSms'] ? '1' : '0');
        // ذخیره تنظیمات FTP
        $registryMGR->update($rootSystem, 'ftp_enabled', $data['ftpEnabled'] ? '1' : '0');
        $registryMGR->update($rootSystem, 'ftp_host', $data['ftpHost'] ?? '');
        $registryMGR->update($rootSystem, 'ftp_port', $data['ftpPort'] ?? '21');
        $registryMGR->update($rootSystem, 'ftp_username', $data['ftpUsername'] ?? '');
        $registryMGR->update($rootSystem, 'ftp_password', $data['ftpPassword'] ?? '');
        $registryMGR->update($rootSystem, 'ftp_path', $data['ftpPath'] ?? '');

        return new JsonResponse([
            'result' => 1,
            'message' => 'Settings saved successfully'
        ]);
    }

    #[Route('/api/admin/registry/settings/test-ftp', name: 'app_registry_settings_test_ftp', methods: ['POST'])]
    public function testFtpConnection(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            // اعتبارسنجی داده‌های ورودی
            $requiredFields = ['host', 'port', 'username', 'password', 'path'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return $this->json([
                        'success' => false,
                        'message' => "فیلد {$field} الزامی است"
                    ], 400);
                }
            }

            // اعتبارسنجی پورت
            $port = (int) $data['port'];
            if ($port < 1 || $port > 65535) {
                return $this->json([
                    'success' => false,
                    'message' => 'پورت باید عددی بین 1 تا 65535 باشد'
                ], 400);
            }

            // ایجاد اتصال FTP
            $ftp = ftp_connect($data['host'], $port, 30);
            if (!$ftp) {
                return $this->json([
                    'success' => false,
                    'message' => 'خطا در اتصال به سرور FTP'
                ], 400);
            }

            // تلاش برای ورود
            if (!ftp_login($ftp, $data['username'], $data['password'])) {
                ftp_close($ftp);
                return $this->json([
                    'success' => false,
                    'message' => 'نام کاربری یا رمز عبور اشتباه است'
                ], 400);
            }

            // تست دسترسی به مسیر
            if (!@ftp_chdir($ftp, $data['path'])) {
                $currentDir = ftp_pwd($ftp); // دریافت مسیر فعلی
                ftp_close($ftp);
                return $this->json([
                    'success' => false,
                    'message' => 'مسیر مورد نظر قابل دسترسی نیست',
                    'suggested_path' => $currentDir // پیشنهاد مسیر فعلی
                ], 400);
            }

            // بستن اتصال
            ftp_close($ftp);

            return $this->json([
                'success' => true,
                'message' => 'اتصال به سرور FTP با موفقیت برقرار شد'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'خطا در تست اتصال: ' . $e->getMessage()
            ], 500);
        }
    }
}