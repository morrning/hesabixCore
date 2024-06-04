<?php

namespace App\Controller;

use App\Entity\InvoiceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends AbstractController
{
    #[Route('/api/invoice/types', name: 'api_invoice_types')]
    public function api_invoice_types(Request $request, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        $items = $entityManagerInterface->getRepository(InvoiceType::class)->findBy(['type' => $params['type']]);
        $res = [];
        foreach ($items as $item) {
            $res[] = [
                'label' => $item->getlabel(),
                'code' => $item->getCode(),
                'checked' => false
            ];
        }
        return $this->json($res);
    }
}
