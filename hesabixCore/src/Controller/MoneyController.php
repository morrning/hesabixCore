<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\HesabdariDoc;
use App\Entity\Money;
use App\Service\Access;
use App\Service\Explore;
use App\Service\Log;
use App\Service\Extractor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MoneyController extends AbstractController
{
    #[Route('/api/money/get/all', name: 'app_money_get_all')]
    public function app_money_get_all(EntityManagerInterface $entityManager): JsonResponse
    {
        $result = $entityManager->getRepository(Money::class)->findAll();
        $out = [];
        foreach ($result as $item) {
            $temp = [];
            $temp['name'] = $item->getName();
            $temp['label'] = $item->getLabel();
            $out[] = $temp;
        }
        return $this->json($out);
    }

    #[Route('/api/money/get/info', name: 'app_money_get_info')]
    public function app_money_get_info(Log $log, Request $request, Extractor $extractor, EntityManagerInterface $entityManager, Access $access): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('name', $params))
            return $this->json($extractor->paramsNotSend());
        $money = $entityManager->getRepository(Money::class)->findOneBy([
            'name' => $params['name']
        ]);
        if (!$money)
            throw $this->createNotFoundException();
        return $this->json(Explore::ExploreMoney($money));

    }
    #[Route('/api/money/remove', name: 'app_money_remove')]
    public function app_money_remove(Log $log, Request $request, Extractor $extractor, EntityManagerInterface $entityManager, Access $access): JsonResponse
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

        $money = $entityManager->getRepository(Money::class)->findOneBy([
            'name' => $params['name']
        ]);
        if (!$money)
            throw $this->createNotFoundException();

        $docs = $entityManager->getRepository(HesabdariDoc::class)->findBy([
            'bid' => $acc['bid'],
            'money' => $money
        ]);
        if (count($docs) != 0) {
            return $this->json($extractor->operationFail('این ارز دارای تاریخچه اسناد حسابداری است.'));
        }
        if ($acc['bid']->getMoney()->getId() == $money->getId()) {
            return $this->json($extractor->operationFail('ارز پیشفرض کسب و کار قابل حذف نیست.'));
        }

        $bid = $entityManager->getRepository(Business::class)->findOneBy([
            'id' => $acc['bid']->getId(),
        ]);
        $bid->removeExtraMoney($money);
        $entityManager->persist($bid);
        $entityManager->flush();
        //add log to system
        $log->insert('تنظیمات پایه', 'یک ارز جانبی با نام  ' . $money->getLabel() . ' حذف شد.', $this->getUser(), $acc['bid']);

        return $this->json($extractor->operationSuccess());
    }

    #[Route('/api/money/add/to/business', name: 'app_money_ad_to_business')]
    public function app_money_ad_to_business(Log $log, Request $request, Extractor $extractor, EntityManagerInterface $entityManager, Access $access): JsonResponse
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

        $money = $entityManager->getRepository(Money::class)->findOneBy([
            'name' => $params['name']
        ]);
        if (!$money)
            throw $this->createNotFoundException();

        foreach ($acc['bid']->getExtraMoney() as $mo) {
            if ($money->getName() == $mo->getName() || $acc['bid']->getMoney()->getName() == $mo->getName()) {
                //added before
                return $this->json($extractor->operationFail('این ارز قبلا افزوده شده است.'));
            }
        }
        $bid = $entityManager->getRepository(Business::class)->findOneBy([
            'id' => $acc['bid']->getId(),
        ]);
        $bid->addExtraMoney($money);
        $entityManager->persist($bid);
        $entityManager->flush();
        //add log to system
        $log->insert('تنظیمات پایه', 'یک ارز جانبی با نام  ' . $money->getLabel() . ' افزوده شد.', $this->getUser(), $acc['bid']);

        return $this->json($extractor->operationSuccess());
    }
}
