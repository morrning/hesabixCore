<?php

namespace App\Controller;

use OpenApi\Annotations as OA;
use App\Entity\BankAccount;
use App\Entity\Cashdesk;
use App\Entity\Commodity;
use App\Entity\HesabdariTable;
use App\Entity\Person;
use App\Entity\Salary;
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
    private $em;
    private $provider;
    function __construct(Provider $provider, EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->provider = $provider;
    }
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
                'bid' => $acc['bid'],
                'money' => $acc['money']
            ]);
        } else {
            $docs = $entityManagerInterface->getRepository(HesabdariDoc::class)->findBy([
                'year' => $acc['year'],
                'bid' => $acc['bid'],
                'type' => $params['type'],
                'money' => $acc['money']
            ]);
        }
        //filter docs by date
        $result = [];
        $dateStart = $provider->shamsiDateToTimestamp($params['dateStart']);
        $dateEnd = $provider->shamsiDateToTimestamp($params['dateEnd']);
        foreach ($docs as $doc) {
            $canAdd = true;
            if ($dateStart) {
                if ($provider->shamsiDateToTimestamp($doc->getDate()) < $dateStart)
                    $canAdd = false;
            }
            if ($dateEnd) {
                if ($provider->shamsiDateToTimestamp($doc->getDate()) > $dateEnd)
                    $canAdd = false;
            }

            if ($canAdd)
                $result[] = $doc;
        }
        $docs = $result;

        $person = $entityManagerInterface->getRepository(Person::class)->findOneBy([
            'bid' => $acc['bid']->getId(),
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
    public function app_report_person_buysell_export_excell(Provider $provider, Access $access, Request $request, EntityManagerInterface $entityManagerInterface): BinaryFileResponse|JsonResponse|StreamedResponse
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
                'bid' => $acc['bid'],
                'money' => $acc['money']
            ]);
        } else {
            $docs = $entityManagerInterface->getRepository(HesabdariDoc::class)->findBy([
                'year' => $acc['year'],
                'bid' => $acc['bid'],
                'type' => $params['type'],
                'money' => $acc['money']
            ]);
        }


        $commodity = $entityManagerInterface->getRepository(Commodity::class)->findOneBy([
            'bid' => $acc['bid']->getId(),
            'code' => $params['commodity'],
        ]);
        //filter docs by date
        $result = [];
        $dateStart = $provider->shamsiDateToTimestamp($params['dateStart']);
        $dateEnd = $provider->shamsiDateToTimestamp($params['dateEnd']);
        foreach ($docs as $doc) {
            $canAdd = true;
            if ($dateStart) {
                if ($provider->shamsiDateToTimestamp($doc->getDate()) < $dateStart)
                    $canAdd = false;
            }
            if ($dateEnd) {
                if ($provider->shamsiDateToTimestamp($doc->getDate()) > $dateEnd)
                    $canAdd = false;
            }

            if ($canAdd)
                $result[] = $doc;
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

    #[Route('/api/report/acc/explore_accounts', name: 'app_report_acc_explore_accounts')]
    public function app_report_acc_explore_accounts(Provider $provider, Jdate $jdate, Access $access, Request $request, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $acc = $access->hasRole('report');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('node', $params))
            throw $this->createNotFoundException();
        if ($params['node'] == 'root') {
            $rootNode = $entityManagerInterface->getRepository(HesabdariTable::class)->findOneBy(['upper' => null]);
        } else {
            $rootNode = $entityManagerInterface->getRepository(HesabdariTable::class)->find($params['node']);
        }
        if (!$rootNode)
            throw $this->createNotFoundException();


        if ($params['node'] == 'root') {
            $tableItems = $entityManagerInterface->getRepository(HesabdariTable::class)->findBy([
                'upper' => 1
            ]);
        } else {
            if ($rootNode->getType() == 'calc') {
                $tableItems = $entityManagerInterface->getRepository(HesabdariTable::class)->findBy([
                    'upper' => $rootNode
                ]);
            } elseif ($rootNode->getType() == 'bank') {
                $tableItems = $entityManagerInterface->getRepository(BankAccount::class)->findBy([
                    'bid' => $acc['bid'],
                    'money' => $acc['money'],
                ]);
            } elseif ($rootNode->getType() == 'cashdesk') {
                $tableItems = $entityManagerInterface->getRepository(Cashdesk::class)->findBy([
                    'bid' => $acc['bid'],
                    'money' => $acc['money'],
                ]);
            } elseif ($rootNode->getType() == 'salary') {
                $tableItems = $entityManagerInterface->getRepository(Salary::class)->findBy([
                    'bid' => $acc['bid'],
                    'money' => $acc['money'],
                ]);
            } elseif ($rootNode->getType() == 'person') {
                $tableItems = $entityManagerInterface->getRepository(Person::class)->findBy([
                    'bid' => $acc['bid'],
                ]);
            }
        }
        $response = [];
        if ($rootNode->getType() == 'calc') {
            foreach ($tableItems as $item) {
                $temp = [
                    'id' => $item->getId(),
                    'account' => $item->getName(),
                    'type' => $item->getType(),
                    'code' => $item->getCode(),
                ];
                $childs = $entityManagerInterface->getRepository(HesabdariTable::class)->findBy([
                    'upper' => $item
                ]);
                $temp['hasChild'] = false;
                if (count($childs) > 0 || $item->getType() != 'calc') {
                    $temp['hasChild'] = true;
                }
                $temp = array_merge($temp, $this->getBalaceTree($acc, $item));
                $response[] = $temp;
            }
        } elseif ($rootNode->getType() == 'bank') {
            foreach ($tableItems as $item) {
                $temp = [
                    'id' => $item->getId(),
                    'account' => $item->getName(),
                    'type' => 'bank',
                    'code' => $item->getCode(),
                ];
                $temp = array_merge($temp, $this->getBalance($acc, $item->getCode(), $rootNode->getType()));
                $temp['hasChild'] = false;
                $response[] = $temp;
            }
        } elseif ($rootNode->getType() == 'cashdesk') {
            foreach ($tableItems as $item) {
                $temp = [
                    'id' => $item->getId(),
                    'account' => $item->getName(),
                    'type' => 'cashdesk',
                    'code' => $item->getCode(),
                ];
                $temp = array_merge($temp, $this->getBalance($acc, $item->getCode(), $rootNode->getType()));
                $temp['hasChild'] = false;
                $response[] = $temp;
            }
        } elseif ($rootNode->getType() == 'salary') {
            foreach ($tableItems as $item) {
                $temp = [
                    'id' => $item->getId(),
                    'account' => $item->getName(),
                    'type' => 'salary',
                    'code' => $item->getCode(),
                ];
                $temp = array_merge($temp, $this->getBalance($acc, $item->getCode(), $rootNode->getType()));
                $temp['hasChild'] = false;
                $response[] = $temp;
            }
        } elseif ($rootNode->getType() == 'person') {
            foreach ($tableItems as $item) {
                $temp = [
                    'id' => $item->getId(),
                    'account' => $item->getNikename(),
                    'type' => 'person',
                    'code' => $item->getCode(),
                ];
                $temp = array_merge($temp, $this->getBalance($acc, $item->getCode(), $rootNode->getType()));
                $temp['hasChild'] = false;
                $response[] = $temp;
            }
        } elseif ($rootNode->getType() == 'commodity') {
            foreach ($tableItems as $item) {
                $temp = [
                    'id' => $item->getId(),
                    'account' => $item->getName(),
                    'type' => 'commodity',
                    'code' => $item->getCode(),
                ];
                $temp = array_merge($temp, $this->getBalance($acc, $item->getCode(), $rootNode->getType()));
                $temp['hasChild'] = false;
                $response[] = $temp;
            }
        }
        $data = [];
        $data['itemData'] = $response;
        $data['tree'] = $this->getTree($rootNode);

        return $this->json($data);
    }

    private function getTree(HesabdariTable $table): array
    {
        $tree = [];
        while ($table->getUpper() != null) {
            $tree[] = [
                'id' => $table->getId(),
                'code' => $table->getCode(),
                'name' => $table->getName(),
            ];
            $table = $table->getUpper();
        }
        $tree[] = [
            'id' => 1,
            'code' => 'root',
            'name' => 'جدول حساب‌ها',
        ];
        return array_reverse($tree);
    }
    private function getBalance($acc, $code, $type = 'calc'): array
    {
        $res = [
            'bal_bd' => 0,
            'bal_bs' => 0,
            'his_bd' => 0,
            'his_bs' => 0,
        ];
        if ($type == 'calc') {
            $calc = $this->em->getRepository(HesabdariTable::class)->findOneBy([
                'code' => $code,
            ]);
            $faltItemsArray = $this->provider->tree2flat($calc);

            //var_dump($faltItemsArray);
            $bs = 0;
            $bd = 0;

            foreach ($faltItemsArray as $item) {
                if ($item['type'] == 'commodity') {
                    $items = $this->em->getRepository(HesabdariRow::class)->findBy([
                        'money' => $acc['money'],
                        'bid' => $acc['bid'],
                        'commodity' => $item['id']
                    ]);
                    foreach ($items as $objItem) {
                        $bs += $objItem->getBs();
                        $bd += $objItem->getBd();
                    }
                } elseif ($item['type'] == 'person') {
                    $items = $this->em->getRepository(HesabdariRow::class)->findBy([
                        'money' => $acc['money'],
                        'bid' => $acc['bid'],
                        'person' => $item['id']
                    ]);
                    foreach ($items as $objItem) {
                        $bs += $objItem->getBs();
                        $bd += $objItem->getBd();
                    }
                } elseif ($item['type'] == 'cashdesk') {
                    $items = $this->em->getRepository(HesabdariRow::class)->findBy([
                        'money' => $acc['money'],
                        'bid' => $acc['bid'],
                        'cashdesk' => $item['id']
                    ]);
                    foreach ($items as $objItem) {
                        $bs += $objItem->getBs();
                        $bd += $objItem->getBd();
                    }
                } elseif ($item['type'] == 'salary') {
                    $items = $this->em->getRepository(HesabdariRow::class)->findBy([
                        'money' => $acc['money'],
                        'bid' => $acc['bid'],
                        'salary' => $item['id']
                    ]);
                    foreach ($items as $objItem) {
                        $bs += $objItem->getBs();
                        $bd += $objItem->getBd();
                    }
                }
            }


        } elseif ($type == 'bank') {
            $bank = $this->em->getRepository(BankAccount::class)->findOneBy([
                'money' => $acc['money'],
                'bid' => $acc['bid'],
                'code' => $code,
            ]);
            $items = $this->em->getRepository(HesabdariRow::class)->findBy([
                'bank' => $bank
            ]);
        } elseif ($type == 'cashdesk') {
            $cashdesk = $this->em->getRepository(Cashdesk::class)->findOneBy([
                'money' => $acc['money'],
                'bid' => $acc['bid'],
                'code' => $code,
            ]);
            $items = $this->em->getRepository(HesabdariRow::class)->findBy([
                'cashdesk' => $cashdesk
            ]);
        } elseif ($type == 'salary') {
            $salary = $this->em->getRepository(Salary::class)->findOneBy([
                'money' => $acc['money'],
                'bid' => $acc['bid'],
                'code' => $code,
            ]);
            $items = $this->em->getRepository(HesabdariRow::class)->findBy([
                'salary' => $salary
            ]);
        } elseif ($type == 'person') {
            $person = $this->em->getRepository(Person::class)->findOneBy([
                'bid' => $acc['bid'],
                'code' => $code,
            ]);
            $items = $this->em->getRepository(HesabdariRow::class)->findBy([
                'person' => $person
            ]);
        }

        foreach ($items as $item) {
            $res['his_bd'] += $item->getBd();
            $res['his_bs'] += $item->getBs();
        }
        if ($res['his_bd'] > $res['his_bs']) {
            $res['bal_bd'] = $res['his_bd'] - $res['his_bs'];
            $res['bal_bs'] = 0;
        } else {
            $res['bal_bs'] = $res['his_bs'] - $res['his_bd'];
            $res['bal_bd'] = 0;
        }
        return $res;
    }

    private function getBalaceTree(array $acc, HesabdariTable $table): array
    {
        $res = [
            'bal_bd' => 0,
            'bal_bs' => 0,
            'his_bd' => 0,
            'his_bs' => 0,
        ];
        if ($this->hasChild($table)) {
            foreach ($this->getChilds($table) as $child) {
                if ($this->hasChild($child)) {
                    $temp = $this->getBalaceTree($acc, $child);
                    $res['his_bd'] += $temp['his_bd'];
                    $res['his_bs'] += $temp['his_bs'];
                } else {
                    $temp = $this->calcBalance($acc, $child);
                    $res['his_bd'] += $temp['his_bd'];
                    $res['his_bs'] += $temp['his_bs'];
                }
            }
        } else {
            $temp = $this->calcBalance($acc, $table);
            $res['his_bd'] += $temp['his_bd'];
            $res['his_bs'] += $temp['his_bs'];
        }

        if ($res['his_bd'] > $res['his_bs']) {
            $res['bal_bd'] = $res['his_bd'] - $res['his_bs'];
            $res['bal_bs'] = 0;
        } else {
            $res['bal_bs'] = $res['his_bs'] - $res['his_bd'];
            $res['bal_bd'] = 0;
        }
        return $res;
    }

    private function calcBalance(array $acc, HesabdariTable $table): array
    {
        $res = [
            'his_bd' => 0,
            'his_bs' => 0,
        ];

        $items = $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
            'bid' => $acc['bid'],
            'ref' => $table
        ], $acc['money']);

        foreach ($items as $objItem) {
            $res['his_bs'] += $objItem->getBs();
            $res['his_bd'] += $objItem->getBd();
        }
        return $res;
    }
    private function hasChild(HesabdariTable $table): bool
    {
        if ($this->em->getRepository(HesabdariTable::class)->findOneBy(['upper' => $table]))
            return true;
        return false;
    }

    private function getChilds(HesabdariTable $table): array
    {
        return $this->em->getRepository(HesabdariTable::class)->findBy(['upper' => $table]);
    }

    #[Route('/api/report/acc/get_details', name: 'app_report_acc_get_details')]
    public function app_report_acc_get_details(Provider $provider, Jdate $jdate, Access $access, Request $request, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $acc = $access->hasRole('report');
        if (!$acc) {
            throw $this->createAccessDeniedException();
        }

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('node', $params))
            throw $this->createNotFoundException();
        if ($params['type'] == 'calc') {
            $rootNode = $entityManagerInterface->getRepository(HesabdariTable::class)->find($params['node']);
            if (!$rootNode)
                throw $this->createNotFoundException();
        } elseif ($params['type'] == 'bank') {
            $rootNode = $entityManagerInterface->getRepository(BankAccount::class)->find($params['node']);
            if (!$rootNode)
                throw $this->createNotFoundException();
        } elseif ($params['type'] == 'cashdesk') {
            $rootNode = $entityManagerInterface->getRepository(Cashdesk::class)->find($params['node']);
            if (!$rootNode)
                throw $this->createNotFoundException();
        } elseif ($params['type'] == 'salary') {
            $rootNode = $entityManagerInterface->getRepository(Salary::class)->find($params['node']);
            if (!$rootNode)
                throw $this->createNotFoundException();
        } elseif ($params['type'] == 'person') {
            $rootNode = $entityManagerInterface->getRepository(Person::class)->find($params['node']);
            if (!$rootNode)
                throw $this->createNotFoundException();
        } elseif ($params['type'] == 'commodity') {
            $rootNode = $entityManagerInterface->getRepository(Commodity::class)->find($params['node']);
            if (!$rootNode)
                throw $this->createNotFoundException();
        }
        $items = $this->tree2flat($rootNode, $acc);
        return $this->json(Explore::ExploreHesabdariRows($items));
    }

    private function tree2flat(Person|HesabdariTable|BankAccount|Cashdesk|Salary $item, array $acc): array
    {
        $res = [];
        if ($this->getEntityName($item) == 'App\Entity\HesabdariTable') {
            if ($this->hasChild($item)) {
                $temp = [];
                foreach ($this->getChilds($item) as $child) {
                    $temp = array_merge($temp, $this->getTree($child));
                }
                $res = array_merge($res, $temp);
            } else {
                $items = $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
                    'bid' => $acc['bid'],
                    'ref' => $item
                ], $acc['money']);
                $res = array_merge($res, $items);
            }
        } elseif ($this->getEntityName($item) == 'App\Entity\BankAccount') {
            $items = $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
                'bid' => $acc['bid'],
                'bank' => $item
            ], $acc['money']);
            $res = array_merge($res, $items);
        } elseif ($this->getEntityName($item) == 'App\Entity\Cashdesk') {
            $items = $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
                'bid' => $acc['bid'],
                'bank' => $item
            ], $acc['money']);
            $res = array_merge($res, $items);
        } elseif ($this->getEntityName($item) == 'App\Entity\Salary') {
            $items = $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
                'bid' => $acc['bid'],
                'salary' => $item
            ], $acc['money']);
            $res = array_merge($res, $items);
        } elseif ($this->getEntityName($item) == 'App\Entity\Person') {
            $items = $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
                'bid' => $acc['bid'],
                'person' => $item
            ], $acc['money']);
            $res = array_merge($res, $items);
        } elseif ($this->getEntityName($item) == 'App\Entity\Commodity') {
            $items = $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
                'bid' => $acc['bid'],
                'commodity' => $item,
            ], $acc['money']);
            $res = array_merge($res, $items);
        }

        return $res;
    }

    /**
     * Returns Doctrine entity name
     *
     * @param mixed $entity
     *
     * @return string
     * @throws \Exception
     */
    private function getEntityName($entity): string
    {
        $entityName = $this->em->getMetadataFactory()->getMetadataFor(get_class($entity))->getName();
        return $entityName;
    }
}
