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

        return new JsonResponse([
            'result' => 1,
            'message' => 'Settings saved successfully'
        ]);
    }
}