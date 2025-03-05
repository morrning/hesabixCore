<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Entity\Cashdesk;
use App\Entity\Cheque;
use App\Entity\Commodity;
use App\Entity\HesabdariRow;
use App\Entity\HesabdariTable;
use App\Entity\Person;
use App\Entity\Salary;
use App\Service\Access;
use App\Service\Explore;
use App\Service\Extractor;
use App\Service\Jdate;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExploreAccountsController extends AbstractController
{
    private $em;
    private $provider;

    public function __construct(EntityManagerInterface $entityManager, Provider $provider)
    {
        $this->em = $entityManager;
        $this->provider = $provider;
    }

    #[Route('/api/report/acc/explore_accounts_det', name: 'app_explore_accounts_det', methods: ['POST'])]
    public function app_explore_accounts_det(Access $access, Request $request, Extractor $extractor): JsonResponse
    {
        $acc = $access->hasRole('report');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        $params = json_decode($request->getContent(), true) ?? [];
        if (!isset($params['node']) || !isset($params['type'])) {
            throw $this->createNotFoundException('Node or type parameter is missing');
        }

        $page = $params['page'] ?? 1;
        $perPage = $params['perPage'] ?? 10;
        $offset = ($page - 1) * $perPage;

        $nodeId = $params['node'] === 'root'
            ? $this->em->getRepository(HesabdariTable::class)
                ->findOneBy(['upper' => null, 'bid' => [$acc['bid']->getId(), null]])->getId()
            : $params['node'];

        $node = $this->em->getRepository(HesabdariTable::class)
            ->findNode($nodeId, $acc['bid']->getId());
        if (!$node) {
            throw $this->createNotFoundException('Node not found');
        }

        $output = [];
        $totalItems = 0;

        switch ($params['type']) {
            case 'calc':
                $query = $this->em->getRepository(HesabdariTable::class)->createQueryBuilder('ht')
                    ->where('ht.upper = :node')
                    ->andWhere('ht.bid = :bid OR ht.bid IS NULL')
                    ->setParameter('node', $node)
                    ->setParameter('bid', $acc['bid']->getId())
                    ->setMaxResults($perPage)
                    ->setFirstResult($offset);
                $children = $query->getQuery()->getResult();
                $totalItems = $query->select('COUNT(ht.id)')->getQuery()->getSingleScalarResult();
                foreach ($children as $child) {
                    $allNodes = $this->getAllDescendants($child, $acc);
                    $allNodes[] = $child;
                    $rows = $this->getRowsForNodes($allNodes, $acc);
                    $output[] = $this->calculateTotals($rows, $child, $acc);
                }
                break;

            case 'bank':
                $query = $this->em->getRepository(BankAccount::class)->createQueryBuilder('b')
                    ->where('b.bid = :bid')
                    ->andWhere('b.money = :money')
                    ->setParameter('bid', $acc['bid'])
                    ->setParameter('money', $acc['money'])
                    ->setMaxResults($perPage)
                    ->setFirstResult($offset);
                $bankAccounts = $query->getQuery()->getResult();
                $totalItems = $query->select('COUNT(b.id)')->getQuery()->getSingleScalarResult();
                foreach ($bankAccounts as $bankAccount) {
                    $rows = $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
                        'bank' => $bankAccount,
                        'ref' => $node,
                        'bid' => $acc['bid'],
                        'year' => $acc['year'],
                    ], $acc['money']);
                    $output[] = $this->calculateBankTotals($rows, $bankAccount, $node);
                }
                break;

            case 'cashdesk':
                $query = $this->em->getRepository(Cashdesk::class)->createQueryBuilder('c')
                    ->where('c.bid = :bid')
                    ->andWhere('c.money = :money')
                    ->setParameter('bid', $acc['bid'])
                    ->setParameter('money', $acc['money'])
                    ->setMaxResults($perPage)
                    ->setFirstResult($offset);
                $cashdesks = $query->getQuery()->getResult();
                $totalItems = $query->select('COUNT(c.id)')->getQuery()->getSingleScalarResult();
                foreach ($cashdesks as $cashdesk) {
                    $rows = $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
                        'cashdesk' => $cashdesk,
                        'ref' => $node,
                        'bid' => $acc['bid'],
                        'year' => $acc['year'],
                    ], $acc['money']);
                    $output[] = $this->calculateCashdeskTotals($rows, $cashdesk, $node);
                }
                break;

            case 'salary':
                $query = $this->em->getRepository(Salary::class)->createQueryBuilder('s')
                    ->where('s.bid = :bid')
                    ->andWhere('s.money = :money')
                    ->setParameter('bid', $acc['bid'])
                    ->setParameter('money', $acc['money'])
                    ->setMaxResults($perPage)
                    ->setFirstResult($offset);
                $salaries = $query->getQuery()->getResult();
                $totalItems = $query->select('COUNT(s.id)')->getQuery()->getSingleScalarResult();
                foreach ($salaries as $salary) {
                    $rows = $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
                        'salary' => $salary,
                        'ref' => $node,
                        'bid' => $acc['bid'],
                        'year' => $acc['year'],
                    ], $acc['money']);
                    $output[] = $this->calculateSalaryTotals($rows, $salary, $node);
                }
                break;

            case 'person':
                $query = $this->em->getRepository(Person::class)->createQueryBuilder('p')
                    ->where('p.bid = :bid')
                    ->setParameter('bid', $acc['bid'])
                    ->setMaxResults($perPage)
                    ->setFirstResult($offset);
                $persons = $query->getQuery()->getResult();
                $totalItems = $query->select('COUNT(p.id)')->getQuery()->getSingleScalarResult();
                foreach ($persons as $person) {
                    $rows = $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
                        'person' => $person,
                        'ref' => $node,
                        'bid' => $acc['bid'],
                        'year' => $acc['year'],
                    ], $acc['money']);
                    $output[] = $this->calculatePersonTotals($rows, $person, $node);
                }
                break;

            case 'commodity':
                $query = $this->em->getRepository(Commodity::class)->createQueryBuilder('c')
                    ->where('c.bid = :bid')
                    ->setParameter('bid', $acc['bid'])
                    ->setMaxResults($perPage)
                    ->setFirstResult($offset);
                $commodities = $query->getQuery()->getResult();
                $totalItems = $query->select('COUNT(c.id)')->getQuery()->getSingleScalarResult();
                foreach ($commodities as $commodity) {
                    $rows = $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
                        'commodity' => $commodity,
                        'ref' => $node,
                        'bid' => $acc['bid'],
                        'year' => $acc['year'],
                    ], $acc['money']);
                    $output[] = $this->calculateCommodityTotals($rows, $commodity, $node);
                }
                break;

            case 'cheque':
                $query = $this->em->getRepository(Cheque::class)->createQueryBuilder('c')
                    ->where('c.bid = :bid')
                    ->setParameter('bid', $acc['bid'])
                    ->setMaxResults($perPage)
                    ->setFirstResult($offset);
                $cheques = $query->getQuery()->getResult();
                $totalItems = $query->select('COUNT(c.id)')->getQuery()->getSingleScalarResult();
                foreach ($cheques as $cheque) {
                    $rows = $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
                        'cheque' => $cheque,
                        'ref' => $node,
                        'bid' => $acc['bid'],
                        'year' => $acc['year'],
                    ], $acc['money']);
                    $output[] = $this->calculateChequeTotals($rows, $cheque, $node);
                }
                break;

            default:
                throw $this->createNotFoundException('Unsupported type');
        }

        return $this->json([
            'itemData' => $output,
            'tree' => $this->getTree($node, $acc),
            'pagination' => [
                'totalItems' => $totalItems,
                'totalPages' => ceil($totalItems / $perPage),
                'currentPage' => $page,
                'perPage' => $perPage
            ]
        ]);
    }

    #[Route('/api/report/acc/get_details', name: 'app_report_acc_get_details', methods: ['POST'])]
    public function app_report_acc_get_details(
        Access $access,
        Request $request,
        Jdate $jdate,
        Explore $explore
    ): JsonResponse {
        $acc = $access->hasRole('report');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        $params = json_decode($request->getContent(), true) ?? [];
        if (!isset($params['node']) || !isset($params['type']) || !isset($params['isObject'])) {
            throw $this->createNotFoundException('Required parameters (node, type, isObject) are missing');
        }

        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = max(1, (int) ($params['perPage'] ?? 10));
        $offset = ($page - 1) * $perPage;

        $rows = [];
        $totalItems = 0;

        $node = $this->em->getRepository(HesabdariTable::class)
            ->findNode($params['upperID'] ?? $params['node'], $acc['bid']->getId());
        if (!$node) {
            throw $this->createNotFoundException('Node not found');
        }

        if ($params['isObject'] === false) {
            $allNodes = $this->getAllDescendants($node, $acc);
            $allNodes[] = $node;
            $qb = $this->em->getRepository(HesabdariRow::class)->createQueryBuilder('r')
                ->innerJoin('r.doc', 'd')
                ->where('r.ref IN (:nodeIds)')
                ->andWhere('r.bid = :bid OR r.bid IS NULL')
                ->andWhere('d.money = :money')
                ->andWhere('r.year = :year')
                ->setParameter('nodeIds', array_map(fn($n) => $n->getId(), $allNodes))
                ->setParameter('bid', $acc['bid'])
                ->setParameter('money', $acc['money'])
                ->setParameter('year', $acc['year']);

            $totalItems = (int) $qb->select('COUNT(r.id)')
                ->getQuery()
                ->getSingleScalarResult();

            $rows = $qb->select('r')
                ->setMaxResults($perPage)
                ->setFirstResult($offset)
                ->getQuery()
                ->getResult();
        } else {
            switch ($params['type']) {
                case 'bank':
                    $item = $this->em->getRepository(BankAccount::class)->findOneBy([
                        'bid' => $acc['bid'],
                        'money' => $acc['money'],
                        'id' => $params['node']
                    ]);
                    if (!$item) {
                        throw $this->createNotFoundException('Bank account not found');
                    }
                    $qb = $this->em->getRepository(HesabdariRow::class)->createQueryBuilder('r')
                        ->innerJoin('r.doc', 'd')
                        ->where('r.bank = :bank')
                        ->andWhere('r.ref = :ref')
                        ->andWhere('r.bid = :bid')
                        ->andWhere('r.year = :year')
                        ->andWhere('d.money = :money')
                        ->setParameter('bank', $item)
                        ->setParameter('ref', $node)
                        ->setParameter('bid', $acc['bid'])
                        ->setParameter('year', $acc['year'])
                        ->setParameter('money', $acc['money']);

                    $totalItems = (int) $qb->select('COUNT(r.id)')
                        ->getQuery()
                        ->getSingleScalarResult();

                    $rows = $qb->select('r')
                        ->setMaxResults($perPage)
                        ->setFirstResult($offset)
                        ->getQuery()
                        ->getResult();
                    break;

                case 'cashdesk':
                    $item = $this->em->getRepository(Cashdesk::class)->findOneBy([
                        'bid' => $acc['bid'],
                        'money' => $acc['money'],
                        'id' => $params['node']
                    ]);
                    if (!$item) {
                        throw $this->createNotFoundException('Cashdesk not found');
                    }
                    $qb = $this->em->getRepository(HesabdariRow::class)->createQueryBuilder('r')
                        ->innerJoin('r.doc', 'd')
                        ->where('r.cashdesk = :cashdesk')
                        ->andWhere('r.ref = :ref')
                        ->andWhere('r.bid = :bid')
                        ->andWhere('r.year = :year')
                        ->andWhere('d.money = :money')
                        ->setParameter('cashdesk', $item)
                        ->setParameter('ref', $node)
                        ->setParameter('bid', $acc['bid'])
                        ->setParameter('year', $acc['year'])
                        ->setParameter('money', $acc['money']);

                    $totalItems = (int) $qb->select('COUNT(r.id)')
                        ->getQuery()
                        ->getSingleScalarResult();

                    $rows = $qb->select('r')
                        ->setMaxResults($perPage)
                        ->setFirstResult($offset)
                        ->getQuery()
                        ->getResult();
                    break;

                case 'salary':
                    $item = $this->em->getRepository(Salary::class)->findOneBy([
                        'bid' => $acc['bid'],
                        'money' => $acc['money'],
                        'id' => $params['node']
                    ]);
                    if (!$item) {
                        throw $this->createNotFoundException('Salary not found');
                    }
                    $qb = $this->em->getRepository(HesabdariRow::class)->createQueryBuilder('r')
                        ->innerJoin('r.doc', 'd')
                        ->where('r.salary = :salary')
                        ->andWhere('r.ref = :ref')
                        ->andWhere('r.bid = :bid')
                        ->andWhere('r.year = :year')
                        ->andWhere('d.money = :money')
                        ->setParameter('salary', $item)
                        ->setParameter('ref', $node)
                        ->setParameter('bid', $acc['bid'])
                        ->setParameter('year', $acc['year'])
                        ->setParameter('money', $acc['money']);

                    $totalItems = (int) $qb->select('COUNT(r.id)')
                        ->getQuery()
                        ->getSingleScalarResult();

                    $rows = $qb->select('r')
                        ->setMaxResults($perPage)
                        ->setFirstResult($offset)
                        ->getQuery()
                        ->getResult();
                    break;

                case 'person':
                    $item = $this->em->getRepository(Person::class)->findOneBy([
                        'bid' => $acc['bid'],
                        'id' => $params['node']
                    ]);
                    if (!$item) {
                        throw $this->createNotFoundException('Person not found');
                    }
                    $qb = $this->em->getRepository(HesabdariRow::class)->createQueryBuilder('r')
                        ->innerJoin('r.doc', 'd')
                        ->where('r.person = :person')
                        ->andWhere('r.ref = :ref')
                        ->andWhere('r.bid = :bid')
                        ->andWhere('r.year = :year')
                        ->andWhere('d.money = :money')
                        ->setParameter('person', $item)
                        ->setParameter('ref', $node)
                        ->setParameter('bid', $acc['bid'])
                        ->setParameter('year', $acc['year'])
                        ->setParameter('money', $acc['money']);

                    $totalItems = (int) $qb->select('COUNT(r.id)')
                        ->getQuery()
                        ->getSingleScalarResult();

                    $rows = $qb->select('r')
                        ->setMaxResults($perPage)
                        ->setFirstResult($offset)
                        ->getQuery()
                        ->getResult();
                    break;

                case 'commodity':
                    $item = $this->em->getRepository(Commodity::class)->findOneBy([
                        'bid' => $acc['bid'],
                        'id' => $params['node']
                    ]);
                    if (!$item) {
                        throw $this->createNotFoundException('Commodity not found');
                    }
                    $qb = $this->em->getRepository(HesabdariRow::class)->createQueryBuilder('r')
                        ->innerJoin('r.doc', 'd')
                        ->where('r.commodity = :commodity')
                        ->andWhere('r.ref = :ref')
                        ->andWhere('r.bid = :bid')
                        ->andWhere('r.year = :year')
                        ->andWhere('d.money = :money')
                        ->setParameter('commodity', $item)
                        ->setParameter('ref', $node)
                        ->setParameter('bid', $acc['bid'])
                        ->setParameter('year', $acc['year'])
                        ->setParameter('money', $acc['money']);

                    $totalItems = (int) $qb->select('COUNT(r.id)')
                        ->getQuery()
                        ->getSingleScalarResult();

                    $rows = $qb->select('r')
                        ->setMaxResults($perPage)
                        ->setFirstResult($offset)
                        ->getQuery()
                        ->getResult();
                    break;

                case 'cheque':
                    $item = $this->em->getRepository(Cheque::class)->findOneBy([
                        'bid' => $acc['bid'],
                        'id' => $params['node']
                    ]);
                    if (!$item) {
                        throw $this->createNotFoundException('Cheque not found');
                    }
                    $qb = $this->em->getRepository(HesabdariRow::class)->createQueryBuilder('r')
                        ->innerJoin('r.doc', 'd')
                        ->where('r.cheque = :cheque')
                        ->andWhere('r.ref = :ref')
                        ->andWhere('r.bid = :bid')
                        ->andWhere('r.year = :year')
                        ->andWhere('d.money = :money')
                        ->setParameter('cheque', $item)
                        ->setParameter('ref', $node)
                        ->setParameter('bid', $acc['bid'])
                        ->setParameter('year', $acc['year'])
                        ->setParameter('money', $acc['money']);

                    $totalItems = (int) $qb->select('COUNT(r.id)')
                        ->getQuery()
                        ->getSingleScalarResult();

                    $rows = $qb->select('r')
                        ->setMaxResults($perPage)
                        ->setFirstResult($offset)
                        ->getQuery()
                        ->getResult();
                    break;

                default:
                    throw $this->createNotFoundException('Unsupported type');
            }
        }

        return $this->json([
            'items' => $explore::ExploreHesabdariRows($rows),
            'pagination' => [
                'totalItems' => $totalItems,
                'totalPages' => ceil($totalItems / $perPage),
                'currentPage' => $page,
                'perPage' => $perPage
            ]
        ]);
    }

    #[Route('/api/report/acc/export_details_excel', name: 'app_report_acc_export_details_excel', methods: ['POST'])]
    public function exportDetailsExcel(Access $access, Request $request, Explore $explore): StreamedResponse
    {
        $acc = $access->hasRole('report');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        $params = json_decode($request->getContent(), true) ?? [];
        if (!isset($params['node']) || !isset($params['type']) || !isset($params['isObject'])) {
            throw $this->createNotFoundException('Required parameters (node, type, isObject) are missing');
        }

        $node = $this->em->getRepository(HesabdariTable::class)
            ->findNode($params['upperID'] ?? $params['node'], $acc['bid']->getId());
        if (!$node) {
            throw $this->createNotFoundException('Node not found');
        }

        $rows = [];
        if ($params['isObject'] === false) {
            $allNodes = $this->getAllDescendants($node, $acc);
            $allNodes[] = $node;
            $rows = $this->em->getRepository(HesabdariRow::class)->createQueryBuilder('r')
                ->innerJoin('r.doc', 'd')
                ->where('r.ref IN (:nodeIds)')
                ->andWhere('r.bid = :bid OR r.bid IS NULL')
                ->andWhere('d.money = :money')
                ->andWhere('r.year = :year')
                ->setParameter('nodeIds', array_map(fn($n) => $n->getId(), $allNodes))
                ->setParameter('bid', $acc['bid'])
                ->setParameter('money', $acc['money'])
                ->setParameter('year', $acc['year'])
                ->getQuery()
                ->getResult();
        } else {
            switch ($params['type']) {
                case 'bank':
                    $item = $this->em->getRepository(BankAccount::class)->findOneBy([
                        'bid' => $acc['bid'],
                        'money' => $acc['money'],
                        'id' => $params['node']
                    ]);
                    if (!$item) {
                        throw $this->createNotFoundException('Bank account not found');
                    }
                    $rows = $this->em->getRepository(HesabdariRow::class)->createQueryBuilder('r')
                        ->innerJoin('r.doc', 'd')
                        ->where('r.bank = :bank')
                        ->andWhere('r.ref = :ref')
                        ->andWhere('r.bid = :bid')
                        ->andWhere('r.year = :year')
                        ->andWhere('d.money = :money')
                        ->setParameter('bank', $item)
                        ->setParameter('ref', $node)
                        ->setParameter('bid', $acc['bid'])
                        ->setParameter('year', $acc['year'])
                        ->setParameter('money', $acc['money'])
                        ->getQuery()
                        ->getResult();
                    break;

                case 'cashdesk':
                    $item = $this->em->getRepository(Cashdesk::class)->findOneBy([
                        'bid' => $acc['bid'],
                        'money' => $acc['money'],
                        'id' => $params['node']
                    ]);
                    if (!$item) {
                        throw $this->createNotFoundException('Cashdesk not found');
                    }
                    $rows = $this->em->getRepository(HesabdariRow::class)->createQueryBuilder('r')
                        ->innerJoin('r.doc', 'd')
                        ->where('r.cashdesk = :cashdesk')
                        ->andWhere('r.ref = :ref')
                        ->andWhere('r.bid = :bid')
                        ->andWhere('r.year = :year')
                        ->andWhere('d.money = :money')
                        ->setParameter('cashdesk', $item)
                        ->setParameter('ref', $node)
                        ->setParameter('bid', $acc['bid'])
                        ->setParameter('year', $acc['year'])
                        ->setParameter('money', $acc['money'])
                        ->getQuery()
                        ->getResult();
                    break;

                case 'salary':
                    $item = $this->em->getRepository(Salary::class)->findOneBy([
                        'bid' => $acc['bid'],
                        'money' => $acc['money'],
                        'id' => $params['node']
                    ]);
                    if (!$item) {
                        throw $this->createNotFoundException('Salary not found');
                    }
                    $rows = $this->em->getRepository(HesabdariRow::class)->createQueryBuilder('r')
                        ->innerJoin('r.doc', 'd')
                        ->where('r.salary = :salary')
                        ->andWhere('r.ref = :ref')
                        ->andWhere('r.bid = :bid')
                        ->andWhere('r.year = :year')
                        ->andWhere('d.money = :money')
                        ->setParameter('salary', $item)
                        ->setParameter('ref', $node)
                        ->setParameter('bid', $acc['bid'])
                        ->setParameter('year', $acc['year'])
                        ->setParameter('money', $acc['money'])
                        ->getQuery()
                        ->getResult();
                    break;

                case 'person':
                    $item = $this->em->getRepository(Person::class)->findOneBy([
                        'bid' => $acc['bid'],
                        'id' => $params['node']
                    ]);
                    if (!$item) {
                        throw $this->createNotFoundException('Person not found');
                    }
                    $rows = $this->em->getRepository(HesabdariRow::class)->createQueryBuilder('r')
                        ->innerJoin('r.doc', 'd')
                        ->where('r.person = :person')
                        ->andWhere('r.ref = :ref')
                        ->andWhere('r.bid = :bid')
                        ->andWhere('r.year = :year')
                        ->andWhere('d.money = :money')
                        ->setParameter('person', $item)
                        ->setParameter('ref', $node)
                        ->setParameter('bid', $acc['bid'])
                        ->setParameter('year', $acc['year'])
                        ->setParameter('money', $acc['money'])
                        ->getQuery()
                        ->getResult();
                    break;

                case 'commodity':
                    $item = $this->em->getRepository(Commodity::class)->findOneBy([
                        'bid' => $acc['bid'],
                        'id' => $params['node']
                    ]);
                    if (!$item) {
                        throw $this->createNotFoundException('Commodity not found');
                    }
                    $rows = $this->em->getRepository(HesabdariRow::class)->createQueryBuilder('r')
                        ->innerJoin('r.doc', 'd')
                        ->where('r.commodity = :commodity')
                        ->andWhere('r.ref = :ref')
                        ->andWhere('r.bid = :bid')
                        ->andWhere('r.year = :year')
                        ->andWhere('d.money = :money')
                        ->setParameter('commodity', $item)
                        ->setParameter('ref', $node)
                        ->setParameter('bid', $acc['bid'])
                        ->setParameter('year', $acc['year'])
                        ->setParameter('money', $acc['money'])
                        ->getQuery()
                        ->getResult();
                    break;

                case 'cheque':
                    $item = $this->em->getRepository(Cheque::class)->findOneBy([
                        'bid' => $acc['bid'],
                        'id' => $params['node']
                    ]);
                    if (!$item) {
                        throw $this->createNotFoundException('Cheque not found');
                    }
                    $rows = $this->em->getRepository(HesabdariRow::class)->createQueryBuilder('r')
                        ->innerJoin('r.doc', 'd')
                        ->where('r.cheque = :cheque')
                        ->andWhere('r.ref = :ref')
                        ->andWhere('r.bid = :bid')
                        ->andWhere('r.year = :year')
                        ->andWhere('d.money = :money')
                        ->setParameter('cheque', $item)
                        ->setParameter('ref', $node)
                        ->setParameter('bid', $acc['bid'])
                        ->setParameter('year', $acc['year'])
                        ->setParameter('money', $acc['money'])
                        ->getQuery()
                        ->getResult();
                    break;

                default:
                    throw $this->createNotFoundException('Unsupported type');
            }
        }

        $data = $explore::ExploreHesabdariRows($rows);

        // تولید فایل اکسل
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // ستون‌ها
        $headers = ['تاریخ', 'شماره', 'شرح', 'بدهکار', 'بستانکار', 'تعداد'];
        $sheet->fromArray($headers, null, 'A1');

        // داده‌ها
        $rowData = [];
        foreach ($data as $index => $item) {
            $rowData[] = [
                $item['date'] ?? '',
                $item['doc_code'] ?? '',
                $item['des'] ?? '',
                $item['bd'] ?? 0,
                $item['bs'] ?? 0,
                $item['commodity_count'] ?? 0,
            ];
        }
        $sheet->fromArray($rowData, null, 'A2');

        // تنظیم پاسخ برای دانلود
        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="details_export.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    /**
     * پیدا کردن زیرمجموعه‌های مستقیم یک نود
     */
    private function getDirectChildren(HesabdariTable $node, array $acc): array
    {
        return $this->em->getRepository(HesabdariTable::class)
            ->findBy(['upper' => $node, 'bid' => [$acc['bid']->getId(), null]]);
    }

    /**
     * پیدا کردن تمام زیرمجموعه‌های یک نود به صورت بازگشتی
     */
    private function getAllDescendants(HesabdariTable $node, array $acc): array
    {
        $descendants = [];
        $children = $this->getDirectChildren($node, $acc);

        foreach ($children as $child) {
            $descendants[] = $child;
            $descendants = array_merge($descendants, $this->getAllDescendants($child, $acc));
        }

        return $descendants;
    }

    /**
     * پیدا کردن ردیف‌های مرتبط با نودها (برای type=calc)
     */
    private function getRowsForNodes(array $nodes, array $acc): array
    {
        $nodeIds = array_unique(array_map(fn($node) => $node->getId(), $nodes));
        return $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
            'ref' => $nodeIds,
            'bid' => $acc['bid'],
            'year' => $acc['year'],
        ], $acc['money']);
    }

    /**
     * محاسبه جمع‌ها برای نودهای calc
     */
    private function calculateTotals(array $rows, HesabdariTable $node, array $acc): array
    {
        $his_bd = 0;
        $his_bs = 0;

        foreach ($rows as $row) {
            $his_bd += (int) $row->getBd();
            $his_bs += (int) $row->getBs();
        }

        $bal_bd = $his_bd > $his_bs ? $his_bd - $his_bs : 0;
        $bal_bs = $his_bs > $his_bd ? $his_bs - $his_bd : 0;

        return [
            'id' => $node->getId(),
            'account' => $node->getName(),
            'type' => $node->getType(),
            'code' => $node->getCode(),
            'name' => $node->getName(),
            'isObject' => false,
            'hasChild' => $this->hasChild($node, $acc),
            'upperID' => $node->getUpper() ? $node->getUpper()->getId() : null,
            'his_bd' => $his_bd,
            'his_bs' => $his_bs,
            'bal_bd' => $bal_bd,
            'bal_bs' => $bal_bs,
        ];
    }

    /**
     * محاسبه جمع‌ها برای حساب‌های بانکی
     */
    private function calculateBankTotals(array $rows, BankAccount $bankAccount, HesabdariTable $node): array
    {
        $his_bd = 0;
        $his_bs = 0;

        foreach ($rows as $row) {
            $his_bd += (int) $row->getBd();
            $his_bs += (int) $row->getBs();
        }

        $bal_bd = $his_bd > $his_bs ? $his_bd - $his_bs : 0;
        $bal_bs = $his_bs > $his_bd ? $his_bs - $his_bd : 0;

        return [
            'id' => $bankAccount->getId(),
            'account' => $bankAccount->getName(),
            'type' => 'bank',
            'code' => $bankAccount->getCode(),
            'name' => $bankAccount->getName(),
            'isObject' => true,
            'hasChild' => false,
            'upperID' => $node->getId(),
            'his_bd' => $his_bd,
            'his_bs' => $his_bs,
            'bal_bd' => $bal_bd,
            'bal_bs' => $bal_bs,
        ];
    }

    /**
     * محاسبه جمع‌ها برای صندوق‌ها
     */
    private function calculateCashdeskTotals(array $rows, Cashdesk $cashdesk, HesabdariTable $node): array
    {
        $his_bd = 0;
        $his_bs = 0;

        foreach ($rows as $row) {
            $his_bd += (int) $row->getBd();
            $his_bs += (int) $row->getBs();
        }

        $bal_bd = $his_bd > $his_bs ? $his_bd - $his_bs : 0;
        $bal_bs = $his_bs > $his_bd ? $his_bs - $his_bd : 0;

        return [
            'id' => $cashdesk->getId(),
            'account' => $cashdesk->getName(),
            'type' => 'cashdesk',
            'code' => $cashdesk->getCode(),
            'name' => $cashdesk->getName(),
            'isObject' => true,
            'hasChild' => false,
            'upperID' => $node->getId(),
            'his_bd' => $his_bd,
            'his_bs' => $his_bs,
            'bal_bd' => $bal_bd,
            'bal_bs' => $bal_bs,
        ];
    }

    /**
     * محاسبه جمع‌ها برای تنخواه‌گردان‌ها
     */
    private function calculateSalaryTotals(array $rows, Salary $salary, HesabdariTable $node): array
    {
        $his_bd = 0;
        $his_bs = 0;

        foreach ($rows as $row) {
            $his_bd += (int) $row->getBd();
            $his_bs += (int) $row->getBs();
        }

        $bal_bd = $his_bd > $his_bs ? $his_bd - $his_bs : 0;
        $bal_bs = $his_bs > $his_bd ? $his_bs - $his_bd : 0;

        return [
            'id' => $salary->getId(),
            'account' => $salary->getName(),
            'type' => 'salary',
            'code' => $salary->getCode(),
            'name' => $salary->getName(),
            'isObject' => true,
            'hasChild' => false,
            'upperID' => $node->getId(),
            'his_bd' => $his_bd,
            'his_bs' => $his_bs,
            'bal_bd' => $bal_bd,
            'bal_bs' => $bal_bs,
        ];
    }

    /**
     * محاسبه جمع‌ها برای اشخاص
     */
    private function calculatePersonTotals(array $rows, Person $person, HesabdariTable $node): array
    {
        $his_bd = 0;
        $his_bs = 0;

        foreach ($rows as $row) {
            $his_bd += (int) $row->getBd();
            $his_bs += (int) $row->getBs();
        }

        $bal_bd = $his_bd > $his_bs ? $his_bd - $his_bs : 0;
        $bal_bs = $his_bs > $his_bd ? $his_bs - $his_bd : 0;

        return [
            'id' => $person->getId(),
            'account' => $person->getNikename(),
            'type' => 'person',
            'code' => $person->getCode(),
            'name' => $person->getNikename(),
            'isObject' => true,
            'hasChild' => false,
            'upperID' => $node->getId(),
            'his_bd' => $his_bd,
            'his_bs' => $his_bs,
            'bal_bd' => $bal_bd,
            'bal_bs' => $bal_bs,
        ];
    }

    /**
     * محاسبه جمع‌ها برای کالاها
     */
    private function calculateCommodityTotals(array $rows, Commodity $commodity, HesabdariTable $node): array
    {
        $his_bd = 0;
        $his_bs = 0;

        foreach ($rows as $row) {
            $his_bd += (int) $row->getBd();
            $his_bs += (int) $row->getBs();
        }

        $bal_bd = $his_bd > $his_bs ? $his_bd - $his_bs : 0;
        $bal_bs = $his_bs > $his_bd ? $his_bs - $his_bd : 0;

        return [
            'id' => $commodity->getId(),
            'account' => $commodity->getName(),
            'type' => 'commodity',
            'code' => $commodity->getCode(),
            'name' => $commodity->getName(),
            'isObject' => true,
            'hasChild' => false,
            'upperID' => $node->getId(),
            'his_bd' => $his_bd,
            'his_bs' => $his_bs,
            'bal_bd' => $bal_bd,
            'bal_bs' => $bal_bs,
        ];
    }

    /**
     * محاسبه جمع‌ها برای چک‌ها
     */
    private function calculateChequeTotals(array $rows, Cheque $cheque, HesabdariTable $node): array
    {
        $his_bd = 0;
        $his_bs = 0;

        foreach ($rows as $row) {
            $his_bd += (int) $row->getBd();
            $his_bs += (int) $row->getBs();
        }

        $bal_bd = $his_bd > $his_bs ? $his_bd - $his_bs : 0;
        $bal_bs = $his_bs > $his_bd ? $his_bs - $his_bd : 0;

        return [
            'id' => $cheque->getId(),
            'account' => $cheque->getNumber(),
            'type' => 'cheque',
            'code' => $cheque->getNumber(),
            'name' => $cheque->getNumber(),
            'isObject' => true,
            'hasChild' => false,
            'upperID' => $node->getId(),
            'his_bd' => $his_bd,
            'his_bs' => $his_bs,
            'bal_bd' => $bal_bd,
            'bal_bs' => $bal_bs,
        ];
    }

    /**
     * بررسی وجود زیرمجموعه (فقط برای type=calc استفاده می‌شود)
     */
    private function hasChild(HesabdariTable $table, array $acc): bool
    {
        return (bool) $this->em->getRepository(HesabdariTable::class)
            ->findOneBy(['upper' => $table, 'bid' => [$acc['bid']->getId(), null]]);
    }

    /**
     * تولید درخت مسیر (breadcrumbs)
     */
    private function getTree(HesabdariTable $table, array $acc): array
    {
        $tree = [];
        $current = $table;

        while ($current) {
            $tree[] = [
                'id' => $current->getId(),
                'code' => $current->getCode(),
                'name' => $current->getName(),
                'hasChild' => $this->hasChild($current, $acc),
            ];
            $current = $current->getUpper();
        }

        $tree[] = [
            'id' => 'root',
            'code' => 'root',
            'name' => 'جدول حساب‌ها',
            'hasChild' => true,
        ];

        return array_reverse($tree);
    }
}