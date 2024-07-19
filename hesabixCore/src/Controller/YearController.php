<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Entity\Year;
use App\Service\Log;
use App\Service\Jdate;
use App\Service\Access;
use App\Entity\Business;
use App\Entity\Cashdesk;
use App\Entity\HesabdariRow;
use App\Entity\Person;
use App\Entity\Salary;
use App\Service\Explore;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class YearController extends AbstractController
{
    public function createDefaultYear(Business $bid,EntityManagerInterface $entityManagerInterface) : Year{
        $year = new Year();
        $year->setBid($bid);
        $year->setHead(true);
        $year->setLabel('سال مالی اول');
        $year->setStart(time());
        $year->setEnd(time() + 31563000);
        $entityManagerInterface->persist($year);
        $entityManagerInterface->flush();
        return $year;

    }
    #[Route('/api/year/list', name: 'app_year_list')]
    public function app_year_list(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $business = $entityManager->getRepository(Business::class)->find($request->headers->get('activeBid'));
        if (!$business)
            throw $this->createNotFoundException();
        $years = $entityManager->getRepository(Year::class)->findBy([
            'bid' => $business
        ]);
        if (count($years) == 0) {
            //no year created create first year
            $years = [$this->createDefaultYear($business,$entityManager)];
        }
        return $this->json($years);
    }

    #[Route('/api/year/get', name: 'app_year_get')]
    public function app_year_get(Jdate $jdate, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $business = $entityManager->getRepository(Business::class)->find($request->headers->get('activeBid'));
        if (!$business)
            throw $this->createNotFoundException();
        $year = $entityManager->getRepository(Year::class)->find($request->headers->get('activeYear'));
        if (!$year)
            throw $this->createNotFoundException();
        $yearLoad = $entityManager->getRepository(Year::class)->findOneBy([
            'id' => $year->getId(),
            'bid' => $business
        ]);
        $yearLoad->setStart($jdate->jdate('Y/m/d', $yearLoad->getStart()));
        $yearLoad->setEnd($jdate->jdate('Y/m/d', $yearLoad->getEnd()));
        $yearLoad->setNow($jdate->jdate('Y/m/d', time()));
        return $this->json($yearLoad);
    }

    #[Route('/api/year/lastyear/info', name: 'app_year_last_year_info')]
    public function app_year_last_year_info(Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('plugAccproCloseYear');
        if (!$acc)
            throw $this->createAccessDeniedException();
        //get all banks for calculate
        $currentYear = $entityManager->getRepository(Year::class)->findOneBy([
            'bid' => $acc['bid'],
            'head' => true
        ]);

        //get all banks for calculate
        $banks = $entityManager->getRepository(BankAccount::class)->findBy([
            'bid' => $acc['bid'],
        ]);
        $banksBs = 0;
        $banksBd = 0;
        foreach ($banks as $item) {
            $rows = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'bank' => $item,
                'year' => $currentYear
            ]);
            foreach ($rows as $row) {
                $banksBd += $row->getBd();
                $banksBs += $row->getBs();
            }
        }

        $response = [];
        $response['banks'] = [
            'bs' => $banksBs,
            'bd' => $banksBd
        ];

        //get all cashdesks for calculate
        $cashdesks = $entityManager->getRepository(Cashdesk::class)->findBy([
            'bid' => $acc['bid'],
        ]);
        $cashdesksBs = 0;
        $cashdesksBd = 0;
        foreach ($cashdesks as $item) {
            $rows = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'cashdesk' => $item,
                'year' => $currentYear
            ]);
            foreach ($rows as $row) {
                $cashdesksBd += $row->getBd();
                $cashdesksBs += $row->getBs();
            }
        }
        $response['cashdesks'] = [
            'bs' => $cashdesksBs,
            'bd' => $cashdesksBd
        ];

        //get all salarys for calculate
        $salarys = $entityManager->getRepository(Salary::class)->findBy([
            'bid' => $acc['bid'],
        ]);
        $salarysBs = 0;
        $salarysBd = 0;
        foreach ($salarys as $item) {
            $rows = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'salary' => $item,
                'year' => $currentYear
            ]);
            foreach ($rows as $row) {
                $salarysBd += $row->getBd();
                $salarysBs += $row->getBs();
            }
        }
        $response['salarys'] = [
            'bs' => $salarysBs,
            'bd' => $salarysBd
        ];

        //get all debtor for calculate
        $persons = $entityManager->getRepository(Person::class)->findBy([
            'bid' => $acc['bid'],
        ]);
        $debtorsBs = 0;
        $debtorsBd = 0;
        foreach ($persons as $item) {
            $rows = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'person' => $item,
                'year' => $currentYear
            ]);
            foreach ($rows as $row) {
                $debtorsBd += $row->getBd();
                $debtorsBs += $row->getBs();
            }
        }
        $response['persons'] = [
            'bs' => $debtorsBs,
            'bd' => $debtorsBd
        ];

        $response['year'] = Explore::ExploreYear($currentYear);
        return $this->json($response);
    }
}
