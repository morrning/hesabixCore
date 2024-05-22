<?php

namespace App\Controller;

use App\Service\Log;
use App\Service\Access;
use App\Service\Explore;
use App\Entity\Commodity;
use App\Service\Provider;
use App\Service\Extractor;
use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Entity\HesabdariTable;
use App\Entity\Person;
use App\Entity\StoreroomTicket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SellController extends AbstractController
{
    #[Route('/api/sell/edit/can/{code}', name: 'app_sell_can_edit')]
    public function app_sell_can_edit(Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, string $code): JsonResponse
    {
        $canEdit = true;
        $acc = $access->hasRole('sell');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
            'bid' => $acc['bid'],
            'code' => $code
        ]);
        //check related documents
        if (count($doc->getRelatedDocs()) != 0)
            $canEdit = false;

        //check storeroom tickets
        $tickets = $entityManager->getRepository(StoreroomTicket::class)->findBy(['doc' => $doc]);
        if (count($tickets) != 0)
            $canEdit = false;
        return $this->json([
            'result' => $canEdit
        ]);
    }

    #[Route('/api/sell/get/info/{code}', name: 'app_sell_get_info')]
    public function app_sell_get_info(Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, string $code): JsonResponse
    {
        $acc = $access->hasRole('sell');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
            'bid' => $acc['bid'],
            'code' => $code
        ]);
        if (!$doc)
            throw $this->createNotFoundException();

        return $this->json(Explore::ExploreSellDoc($doc));
    }

    #[Route('/api/sell/mod', name: 'app_sell_mod')]
    public function app_sell_mod(Provider $provider, Extractor $extractor, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        $acc = $access->hasRole('sell');
        if (!$acc)
            throw $this->createAccessDeniedException();

        if (!array_key_exists('update', $params)) {
            return $this->json($extractor->paramsNotSend());
        }
        if ($params['update'] != '') {
            $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                'bid' => $acc['bid'],
                'year' => $acc['year'],
                'code' => $params['update']
            ]);
            if (!$doc) $this->json($extractor->notFound());

            $rows = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'doc' => $doc
            ]);
            foreach ($rows as $row)
                $entityManager->remove($row);
        } else {
            $doc = new HesabdariDoc();
            $doc->setBid($acc['bid']);
            $doc->setYear($acc['year']);
            $doc->setDateSubmit(time());
            $doc->setType('sell');
            $doc->setSubmitter($this->getUser());
            $doc->setMoney($acc['bid']->getMoney());
            $doc->setCode($provider->getAccountingCode($acc['bid'], 'accounting'));
        }
        $doc->setDes($params['des']);
        $doc->setDate($params['date']);
        $sumTax = 0;
        $sumTotal = 0;
        foreach ($params['rows'] as $row) {
            $sumTax += $row['tax'];
            $sumTotal += $row['sumWithoutTax'];
            $hesabdariRow = new HesabdariRow();
            $hesabdariRow->setDes($row['des']);
            $hesabdariRow->setBid($acc['bid']);
            $hesabdariRow->setYear($acc['year']);
            $hesabdariRow->setDoc($doc);
            $hesabdariRow->setBs($row['sumWithoutTax'] + $row['tax']);
            $hesabdariRow->setBd(0);
            $hesabdariRow->setDiscount($row['discount']);
            $hesabdariRow->setTax($row['tax']);
            $ref = $entityManager->getRepository(HesabdariTable::class)->findOneBy([
                'code' => '59' // sell commodity
            ]);
            $hesabdariRow->setRef($ref);
            $row['count'] = str_replace(',', '', $row['count']);
            $commodity = $entityManager->getRepository(Commodity::class)->findOneBy([
                'id' => $row['commodity']['id'],
                'bid' => $acc['bid']
            ]);
            if (!$commodity)
                return $this->json($extractor->paramsNotSend());
            $hesabdariRow->setCommodity($commodity);
            $hesabdariRow->setCommdityCount($row['count']);
            $entityManager->persist($hesabdariRow);
        }
        //set amount of document
        $doc->setAmount($sumTax + $sumTotal);
        //set person buyer
        $hesabdariRow = new HesabdariRow();
        $hesabdariRow->setDes('فاکتور فروش');
        $hesabdariRow->setBid($acc['bid']);
        $hesabdariRow->setYear($acc['year']);
        $hesabdariRow->setDoc($doc);
        $hesabdariRow->setBs(0);
        $hesabdariRow->setBd($sumTax + $sumTotal);
        $ref = $entityManager->getRepository(HesabdariTable::class)->findOneBy([
            'code' => '3' // persons
        ]);
        $hesabdariRow->setRef($ref);
        $person = $entityManager->getRepository(Person::class)->findOneBy([
            'bid' => $acc['bid'],
            'code' => $params['buyer']['code']
        ]);
        if (!$person)
            return $this->json($extractor->paramsNotSend());
        $hesabdariRow->setPerson($person);
        $entityManager->persist($hesabdariRow);

        //set tax info
        
        $entityManager->persist($doc);
        $entityManager->flush();
        $log->insert(
            'حسابداری',
            'سند حسابداری شماره ' . $doc->getCode() . ' ثبت / ویرایش شد.',
            $this->getUser(),
            $request->headers->get('activeBid'),
            $doc
        );
        return $this->json($extractor->operationSuccess());
    }
}
