<?php

namespace App\Service;

use App\Entity\Business;
use App\Entity\AccountingPackageOrder;
use Doctrine\ORM\EntityManagerInterface;

class AccountingPermissionService
{
    private EntityManagerInterface $entityManager;
    private registryMGR $registryMGR;

    public function __construct(EntityManagerInterface $entityManager, registryMGR $registryMGR)
    {
        $this->entityManager = $entityManager;
        $this->registryMGR = $registryMGR;
    }

    private function getAccountingDocPrice(): int
    {
        $rootSystem = 'system_settings';
        return (int) $this->registryMGR->get($rootSystem, 'accounting_doc_price');
    }

    /**
     * چک می‌کنه که آیا کسب‌وکار می‌تونه سند حسابداری ثبت کنه یا نه
     *
     * @param Business $business
     * @return array{result: bool, message: string, code: int}
     */
    public function canRegisterAccountingDoc(Business $business): array
    {
        $rootSystem = 'system_settings';

        // ۱. چک کردن ثبت رایگان سند حسابداری
        if ($this->registryMGR->get($rootSystem, 'canFreeAccounting') === true) {
            return [
                'result' => true,
                'message' => 'ثبت سند حسابداری به صورت رایگان مجاز است.',
                'code' => 1
            ];
        }

        // ۲. چک کردن پکیج حسابداری فعال (با وضعیت پرداخت‌شده)
        $currentTime = time();
        $packageOrders = $this->entityManager->getRepository(AccountingPackageOrder::class)->findBy([
            'bid' => $business->getId(),
            'status' => 100 // فقط پکیج‌های پرداخت‌شده
        ]);

        foreach ($packageOrders as $order) {
            if ((int) $order->getDateExpire() > $currentTime) {
                return [
                    'result' => true,
                    'message' => 'کسب‌وکار دارای پکیج فعال حسابداری است.',
                    'code' => 2
                ];
            }
        }

        // ۳. چک کردن اعتبار موجود (smsCharge) و کسر هزینه
        $accountingDocPrice = $this->getAccountingDocPrice();
        $smsCharge = (int) $business->getSmsCharge();
        if ($smsCharge >= $accountingDocPrice) {
            // کسر هزینه از اعتبار
            $business->setSmsCharge((string) ($smsCharge - $accountingDocPrice));
            $this->entityManager->persist($business);
            $this->entityManager->flush();

            return [
                'result' => true,
                'message' => 'هزینه سند حسابداری از اعتبار کسر شد.',
                'code' => 3
            ];
        }

        // ۴. اگه هیچ‌کدوم از شرط‌ها برقرار نبود
        return [
            'result' => false,
            'message' => 'اعتبار کافی برای ثبت سند حسابداری وجود ندارد. لطفاً اعتبار خود را افزایش دهید یا پکیج حسابداری خریداری کنید.',
            'code' => 4
        ];
    }
}