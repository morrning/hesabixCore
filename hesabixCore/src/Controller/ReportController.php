<?php

namespace App\Controller;

use App\Entity\Commodity;
use App\Entity\Person;
use App\Service\Access;
use App\Service\pdfMGR;
use App\Service\Provider;
use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Service\Explore;
use App\Service\Jdate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReportController extends AbstractController
{
    #[Route('/api/report/person/buysell', name: 'app_report_person_buysell')]
    public function app_report_person_buysell(Provider $provider, Jdate $jdate, Access $access, Request $request, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $acc = $access->hasRole('report');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if ($params['type'] == 'all') {
            $docs = $entityManagerInterface->getRepository(HesabdariDoc::class)->findBy([
                'year' => $acc['year'],
                'bid'  => $acc['bid'],
            ]);
        } else {
            $docs = $entityManagerInterface->getRepository(HesabdariDoc::class)->findBy([
                'year' => $acc['year'],
                'bid'  => $acc['bid'],
                'type' => $params['type'],
            ]);
        }
        //filter docs by date
        $result = [];
        $dateStart = $provider->shamsiDateToTimestamp($params['dateStart']);
        $dateEnd = $provider->shamsiDateToTimestamp($params['dateEnd']);
        foreach ($docs as $doc) {
            $canAdd = true;
            if ($dateStart) {
                if ($provider->shamsiDateToTimestamp($doc->getDate()) < $dateStart) $canAdd = false;
            }
            if ($dateEnd) {
                if ($provider->shamsiDateToTimestamp($doc->getDate()) > $dateEnd) $canAdd = false;
            }

            if ($canAdd) $result[] = $doc;
        }
        $docs = $result;

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
                'rowId' => $item->getId(),
                'code' => $item->getCommodity()->getCode(),
                'khadamat' => $item->getCommodity()->isKhadamat(),
                'name' => $item->getCommodity()->getName(),
                'unit' => $item->getCommodity()->getUnit()->getName(),
                'count' => $item->getCommdityCount(),
                'date' => $item->getDoc()->getDate(),
                'docCode' => $item->getDoc()->getCode(),
                'type' => $item->getDoc()->getType()
            ];
            if ($item->getDoc()->getType() == 'buy' || $item->getDoc()->getType() == 'rfsell') {
                $temp['priceAll'] = $item->getBd();
            } elseif ($item->getDoc()->getType() == 'sell' || $item->getDoc()->getType() == 'rfbuy') {
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

    #[Route('/api/report/person/buysell/export/excel', name: 'app_report_person_buysell_export_excell')]
    public function app_report_person_buysell_export_excell(Provider $provider, Access $access, Request $request, EntityManagerInterface $entityManagerInterface): BinaryFileResponse | JsonResponse | StreamedResponse
    {
        $acc = $access->hasRole('report');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        $items = [];
        foreach ($params['items'] as $param) {
            $prs = $entityManagerInterface->getRepository(HesabdariRow::class)->findOneBy([
                'id' => $param['rowId'],
                'bid' => $acc['bid']
            ]);
            if ($prs)
                $items[] = $prs;
        }

        $response = [];
        foreach ($items as $item) {
            $temp = [
                'id' => $item->getCommodity()->getId(),
                'code' => $item->getCommodity()->getCode(),
                'khadamat' => $item->getCommodity()->isKhadamat(),
                'name' => $item->getCommodity()->getName(),
                'unit' => $item->getCommodity()->getUnit()->getName(),
                'count' => $item->getCommdityCount(),
                'date' => $item->getDoc()->getDate(),
                'docCode' => $item->getDoc()->getCode(),
                'type' => $item->getDoc()->getType()
            ];
            if ($item->getDoc()->getType() == 'buy') {
                $temp['priceAll'] = $item->getBd();
            } elseif ($item->getDoc()->getType() == 'sell') {
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
        $labels = [
            'کد حسابداری'
        ];
        return new BinaryFileResponse($provider->createExcellFromArray($response));
    }

    #[Route('/api/report/commodity/buysell', name: 'app_report_commodity_buysell')]
    public function app_report_commodity_buysell(Provider $provider, Jdate $jdate, Access $access, Request $request, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $acc = $access->hasRole('report');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if ($params['type'] == 'all') {
            $docs = $entityManagerInterface->getRepository(HesabdariDoc::class)->findBy([
                'year' => $acc['year'],
                'bid'  => $acc['bid'],
            ]);
        } else {
            $docs = $entityManagerInterface->getRepository(HesabdariDoc::class)->findBy([
                'year' => $acc['year'],
                'bid'  => $acc['bid'],
                'type' => $params['type'],
            ]);
        }


        $commodity = $entityManagerInterface->getRepository(Commodity::class)->findOneBy([
            'bid'  => $acc['bid']->getId(),
            'code' => $params['commodity'],
        ]);
        //filter docs by date
        $result = [];
        $dateStart = $provider->shamsiDateToTimestamp($params['dateStart']);
        $dateEnd = $provider->shamsiDateToTimestamp($params['dateEnd']);
        foreach ($docs as $doc) {
            $canAdd = true;
            if ($dateStart) {
                if ($provider->shamsiDateToTimestamp($doc->getDate()) < $dateStart) $canAdd = false;
            }
            if ($dateEnd) {
                if ($provider->shamsiDateToTimestamp($doc->getDate()) > $dateEnd) $canAdd = false;
            }

            if ($canAdd) $result[] = $doc;
        }
        $docs = $result;
        $result = [];
        foreach ($docs as $doc) {
            $rows = $doc->getHesabdariRows();
            foreach ($rows as $row) {
                if ($row->getCommodity())
                    if ($row->getCommodity()->getId() == $commodity->getId()) {
                        $result[] = $row;
                    }
            }
        }

        $response = [];
        foreach ($result as $item) {
            $temp = [
                'id' => $item->getCommodity()->getId(),
                'rowId' => $item->getId(),
                'code' => $item->getCommodity()->getCode(),
                'khadamat' => $item->getCommodity()->isKhadamat(),
                'name' => $item->getCommodity()->getName(),
                'unit' => $item->getCommodity()->getUnit()->getName(),
                'count' => $item->getCommdityCount(),
                'date' => $item->getDoc()->getDate(),
                'docCode' => $item->getDoc()->getCode(),
                'type' => $item->getDoc()->getType(),
            ];
            if ($item->getDoc()->getType() == 'buy' || $item->getDoc()->getType() == 'rfsell') {
                $temp['priceAll'] = $item->getBd();
            } elseif ($item->getDoc()->getType() == 'sell' || $item->getDoc()->getType() == 'rfbuy') {
                $temp['priceAll'] = $item->getBs();
            }
            if ($temp['count'] != 0) {
                $temp['priceOne'] = $temp['priceAll'] / $temp['count'];
                $temp['priceAll'] = number_format($temp['priceAll']);
                $temp['priceOne'] = number_format($temp['priceOne']);
                $temp['count'] = number_format($temp['count']);
            }
            //find person
            foreach ($item->getDoc()->getHesabdariRows() as $rw) {
                if ($rw->getPerson()) {
                    $temp['person'] = Explore::ExplorePerson($rw->getPerson());
                }
            }
            $response[] = $temp;
        }
        return $this->json($response);
    }
}
