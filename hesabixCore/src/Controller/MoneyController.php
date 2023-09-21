<?php

namespace App\Controller;

use App\Entity\Money;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MoneyController extends AbstractController
{
    #[Route('/api/money/get/all', name: 'app_money_get_all')]
    public function app_money_get_all(EntityManagerInterface $entityManager): JsonResponse
    {
        $result = $entityManager->getRepository(Money::class)->findAll();
        $out = [];
        foreach ($result as $item){
            $temp = [];
            $temp['name'] = $item->getName();
            $temp['label'] = $item->getLabel();
            $out[] = $temp;
        }
        return $this->json($out);
    }
}
