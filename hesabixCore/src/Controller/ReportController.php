<?php

namespace App\Controller;

use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Entity\Person;
use App\Service\Access;
use App\Service\pdfMGR;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ReportController extends AbstractController
{
    #[Route('/api/report/person/buysell', name: 'app_report_person_buysell')]
    public function app_report_person_buysell(Access $access, Request $request, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $acc = $access->hasRole('reports');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        $docs = $entityManagerInterface->getRepository(HesabdariDoc::class)->findBy([
            'year' => $acc['year'],
            'bid'  => $acc['bid'],
            'type' => $params['type'],
        ]);

        $person = $entityManagerInterface->getRepository(Person::class)->findOneBy([
            'bid'  => $acc['bid']->getId(),
            'code' => $params['person'],
        ]);
        $result = [];
        foreach ($docs as $doc) {
            $rows = $doc->getHesabdariRows();
            foreach ($rows as $row) {
                if ($row->getPerson()) {
                    if ($person->getId() == $row->getPerson()->getId()) {
                        $result[] = $doc;
                    }
                }
            }
        }
        $docs = $result;
        $result = [];
        foreach ($docs as $doc) {
            $rows = $doc->getHesabdariRows();
            foreach ($rows as $row) {
                if ($row->getCommodity()) {
                    $result[] = $row;
                }
            }
        }
        $response = [];
        foreach ($result as $item) {
            $temp = [
                'id' => $item->getCommodity()->getId(),
                'code' => $item->getCommodity()->getCode(),
                'khadamat' => $item->getCommodity()->isKhadamat(),
                'name' => $item->getCommodity()->getName(),
                'unit' => $item->getCommodity()->getUnit()->getName(),
                'count' => $item->getCommdityCount(),
            ];
            if ($params['type'] == 'buy') {
                $temp['priceAll'] = $item->getBd();
            } elseif ($params['type'] == 'sell') {
                $temp['priceAll'] = $item->getBs();
            }
            if ($temp['count'] != 0) {
                $temp['priceOne'] = $temp['priceAll'] / $temp['count'];
                $temp['priceAll'] = number_format($temp['priceAll']);
                $temp['priceOne'] = number_format($temp['priceOne']);
                $temp['count'] = number_format($temp['count']);
                $response[] = $temp;
            }
        }
        return $this->json($response);
    }
}
