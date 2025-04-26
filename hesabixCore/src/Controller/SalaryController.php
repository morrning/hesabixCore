<?php

namespace App\Controller;

use App\Entity\HesabdariRow;
use App\Entity\Salary;
use App\Service\Access;
use App\Service\Explore;
use App\Service\Log;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SalaryController extends AbstractController
{
    #[Route('/api/salary/list', name: 'app_salary_list')]
    public function app_salary_list(Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('salary');
        if (!$acc)
            throw $this->createAccessDeniedException();
        //bug fix for bank with no money type
        $datas = $entityManager->getRepository(Salary::class)->findBy([
            'bid' => $request->headers->get('activeBid'),
            'money' => null
        ]);
        foreach ($datas as $data) {
            $data->setMoney($acc['bid']->getMoney());
            $entityManager->persist($data);
        }
        $entityManager->flush();
        //end of bug fix

        $datas = $entityManager->getRepository(Salary::class)->findBy([
            'bid' => $request->headers->get('activeBid'),
            'money' => $acc['money']
        ]);
        $resp = [];
        foreach ($datas as $data) {
            $bs = 0;
            $bd = 0;
            $items = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'salary' => $data
            ]);
            foreach ($items as $item) {
                $bs += $item->getBs();
                $bd += $item->getBd();
            }
            $data->setBalance($bd - $bs);
            $resp[] = Explore::ExploreSalary($data);
        }
        return $this->json($resp);
    }

    #[Route('/api/salary/info/{code}', name: 'app_salary_info')]
    public function app_salary_info($code, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('salary');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $data = $entityManager->getRepository(Salary::class)->findOneBy([
            'bid' => $acc['bid'],
            'code' => $code
        ]);
        return $this->json(Explore::ExploreSalary($data));
    }

    #[Route('/api/salary/mod/{code}', name: 'app_salary_mod')]
    public function app_salary_mod(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('salary');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('name', $params))
            return $this->json(['result' => -1]);
        if (count_chars(trim($params['name'])) == 0)
            return $this->json(['result' => 3]);
        if ($code == 0) {
            $data = $entityManager->getRepository(Salary::class)->findOneBy([
                'name' => $params['name'],
                'bid' => $acc['bid']
            ]);
            //check exist before
            if ($data)
                return $this->json(['result' => 2]);
            $data = new Salary();
            $data->setCode($provider->getAccountingCode($request->headers->get('activeBid'), 'salary'));
            $data->setMoney($acc['money']);
        } else {
            $data = $entityManager->getRepository(Salary::class)->findOneBy([
                'bid' => $acc['bid'],
                'code' => $code
            ]);
            if (!$data)
                throw $this->createNotFoundException();
            if (!$data->getMoney()) {
                $data->setMoney($acc['money']);
            }
        }
        $data->setBid($acc['bid']);
        $data->setname($params['name']);
        $data->setDes($params['des']);
        $entityManager->persist($data);
        $entityManager->flush();
        $log->insert('بانک', 'تنخواه گردان با نام  ' . $params['name'] . ' افزوده/ویرایش شد.', $this->getUser(), $request->headers->get('activeBid'));
        return $this->json(['result' => 1]);
    }

    #[Route('/api/salary/delete/{code}', name: 'app_salary_delete')]
    public function app_salary_delete(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('salary');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $salary = $entityManager->getRepository(Salary::class)->findOneBy(['bid' => $acc['bid'], 'code' => $code]);
        if (!$salary)
            throw $this->createNotFoundException();
        //check accounting docs
        $rows = $entityManager->getRepository(HesabdariRow::class)->findby(['bid' => $acc['bid'], 'salary' => $salary]);
        if (count($rows) > 0)
            return $this->json(['result' => 2]);

        $name = $salary->getName();
        $entityManager->remove($salary);
        $log->insert('بانکداری', '  تنخواه‌گردان  با نام ' . $name . ' حذف شد. ', $this->getUser(), $acc['bid']->getId());
        return $this->json(['result' => 1]);
    }

    #[Route('/api/salary/search', name: 'app_salary_search')]
    public function app_salary_search(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('salary');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        $query = $entityManager->createQueryBuilder()
            ->select('s')
            ->from(Salary::class, 's')
            ->where('s.bid = :bid')
            ->andWhere('s.money = :money')
            ->setParameter('bid', $acc['bid'])
            ->setParameter('money', $acc['money']);

        if (isset($params['search']) && !empty($params['search'])) {
            $query->andWhere('s.name LIKE :search')
                ->setParameter('search', '%' . $params['search'] . '%');
        }

        if (isset($params['page']) && isset($params['itemsPerPage'])) {
            $query->setFirstResult(($params['page'] - 1) * $params['itemsPerPage'])
                ->setMaxResults($params['itemsPerPage']);
        }

        $datas = $query->getQuery()->getResult();
        
        foreach ($datas as $data) {
            $bs = 0;
            $bd = 0;
            $items = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'salary' => $data
            ]);
            foreach ($items as $item) {
                $bs += $item->getBs();
                $bd += $item->getBd();
            }
            $data->setBalance($bd - $bs);
        }

        return $this->json([
            'items' => $provider->ArrayEntity2Array($datas, 0),
            'total' => count($datas)
        ]);
    }

    #[Route('/api/salary/balance/{code}', name: 'app_salary_balance')]
    public function app_salary_balance($code, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('salary');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $salary = $entityManager->getRepository(Salary::class)->findOneBy([
            'bid' => $acc['bid'],
            'money' => $acc['money'],
            'code' => $code
        ]);

        if (!$salary)
            throw $this->createNotFoundException();

        $bs = 0;
        $bd = 0;
        $items = $entityManager->getRepository(HesabdariRow::class)->findBy([
            'salary' => $salary
        ]);

        foreach ($items as $item) {
            $bs += $item->getBs();
            $bd += $item->getBd();
        }

        return $this->json([
            'balance' => $bd - $bs,
            'debit' => $bd,
            'credit' => $bs
        ]);
    }

    #[Route('/api/salary/transactions/{code}', name: 'app_salary_transactions')]
    public function app_salary_transactions($code, Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('salary');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        $salary = $entityManager->getRepository(Salary::class)->findOneBy([
            'bid' => $acc['bid'],
            'money' => $acc['money'],
            'code' => $code
        ]);

        if (!$salary)
            throw $this->createNotFoundException();

        $query = $entityManager->createQueryBuilder()
            ->select('r')
            ->from(HesabdariRow::class, 'r')
            ->where('r.salary = :salary')
            ->andWhere('r.bid = :bid')
            ->setParameter('salary', $salary)
            ->setParameter('bid', $acc['bid']);

        if (isset($params['startDate']) && isset($params['endDate'])) {
            $query->andWhere('r.doc.date BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $params['startDate'])
                ->setParameter('endDate', $params['endDate']);
        }

        if (isset($params['page']) && isset($params['itemsPerPage'])) {
            $query->setFirstResult(($params['page'] - 1) * $params['itemsPerPage'])
                ->setMaxResults($params['itemsPerPage']);
        }

        $transactions = $query->getQuery()->getResult();

        return $this->json([
            'items' => $provider->ArrayEntity2Array($transactions, 0),
            'total' => count($transactions)
        ]);
    }
}
