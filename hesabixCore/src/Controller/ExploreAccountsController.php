<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Entity\Cashdesk;
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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExploreAccountsController extends AbstractController
{
    private $em;
    private $provider;
    function __construct(Provider $provider, EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->provider = $provider;
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

        if ($params['isObject'] == false) {
            $node = $entityManagerInterface->getRepository(HesabdariTable::class)->findNode($params['node'], $acc['bid']->getId());
            if (!$node)
                throw $this->createNotFoundException();
            $rows = $this->tree2flat($node, $acc);
        } else {
            $node = $entityManagerInterface->getRepository(HesabdariTable::class)->findNode($params['upperID'], $acc['bid']->getId());
            if (!$node)
                throw $this->createNotFoundException();
            if ($node->getType() == 'bank') {
                $item = $entityManagerInterface->getRepository(BankAccount::class)->findOneBy([
                    'bid' => $acc['bid'],
                    'money' => $acc['money'],
                    'id' => $params['node']
                ]);
                $rows = $this->getAllBanksRows($node, $acc, [$item]);
            } elseif ($node->getType() == 'cashdesk') {
                $item = $entityManagerInterface->getRepository(Cashdesk::class)->findOneBy([
                    'bid' => $acc['bid'],
                    'money' => $acc['money'],
                    'id' => $params['node']
                ]);
                $rows = $this->getAllCashdeskRows($node, $acc, [$item]);
            } elseif ($node->getType() == 'salary') {
                $item = $entityManagerInterface->getRepository(Salary::class)->findOneBy([
                    'bid' => $acc['bid'],
                    'money' => $acc['money'],
                    'id' => $params['node']
                ]);
                $rows = $this->getAllSalarysRows($node, $acc, [$item]);
            } elseif ($node->getType() == 'person') {
                $item = $entityManagerInterface->getRepository(Person::class)->findOneBy([
                    'bid' => $acc['bid'],
                    'id' => $params['node']
                ]);
                $rows = $this->getAllPersonsRows($node, $acc, [$item]);

            } elseif ($node->getType() == 'commodity') {
                $item = $entityManagerInterface->getRepository(Commodity::class)->findOneBy([
                    'bid' => $acc['bid'],
                    'id' => $params['node']
                ]);
                $rows = $this->getAllCommoditiesRows($node, $acc, [$item]);
            }
        }
        return $this->json(Explore::ExploreHesabdariRows($rows));
    }

    #[Route('/api/report/acc/explore_accounts_det', name: 'app_explore_accounts_det')]
    public function app_explore_accounts_det(Access $access, Request $request, EntityManagerInterface $entityManagerInterface, Extractor $extractor): JsonResponse
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
            $params['node'] = $entityManagerInterface
                ->getRepository(HesabdariTable::class)
                ->findOneBy(['upper' => null])
                ->getId();
        }

        $node = $entityManagerInterface->getRepository(HesabdariTable::class)->findNode($params['node'], $acc['bid']->getId());
        if (!$node)
            throw $this->createNotFoundException();

        if ($node->getType() == 'calc') {
            $childs = $this->getChilds($node, $acc);
            $output = [];
            foreach ($childs as $child) {
                $childRows = $this->tree2flat($child, $acc);
                $output[] = $this->calculateOutput($childRows, $child, $acc);
            }
        } elseif ($node->getType() == 'bank') {
            $items = $entityManagerInterface->getRepository(BankAccount::class)->findBy([
                'bid' => $acc['bid'],
                'money' => $acc['money'],
            ]);
            foreach ($items as $item) {
                $rows = $this->getAllBanksRows($node, $acc, [$item]);
                $output[] = $this->calculateOutputObject($rows, $node, $acc, $item);
            }
        } elseif ($node->getType() == 'cashdesk') {
            $items = $entityManagerInterface->getRepository(Cashdesk::class)->findBy([
                'bid' => $acc['bid'],
                'money' => $acc['money'],
            ]);
            foreach ($items as $item) {
                $rows = $this->getAllCashdeskRows($node, $acc, [$item]);
                $output[] = $this->calculateOutputObject($rows, $node, $acc, $item);
            }
        } elseif ($node->getType() == 'salary') {
            $items = $entityManagerInterface->getRepository(Salary::class)->findBy([
                'bid' => $acc['bid'],
                'money' => $acc['money'],
            ]);
            foreach ($items as $item) {
                $rows = $this->getAllSalarysRows($node, $acc, [$item]);
                $output[] = $this->calculateOutputObject($rows, $node, $acc, $item);
            }
        } elseif ($node->getType() == 'person') {
            $items = $entityManagerInterface->getRepository(Person::class)->findBy([
                'bid' => $acc['bid'],
            ]);
            foreach ($items as $item) {
                $rows = $this->getAllPersonsRows($node, $acc, [$item]);
                $output[] = $this->calculateOutputObject($rows, $node, $acc, $item);
            }
        } elseif ($node->getType() == 'commodity') {
            $items = $entityManagerInterface->getRepository(Commodity::class)->findBy([
                'bid' => $acc['bid'],
            ]);
            foreach ($items as $item) {
                $rows = $this->getAllCommoditiesRows($node, $acc, [$item]);
                $output[] = $this->calculateOutputObject($rows, $node, $acc, $item);
            }
        }

        $data = [];
        $data['itemData'] = $output;
        $data['tree'] = $this->getTree($node);
        return $this->json($data);
    }
    private function tree2flat(Person|HesabdariTable|BankAccount|Cashdesk|Salary $item, array $acc): array
    {
        $res = [];
        if ($this->getEntityName($item) == 'App\Entity\HesabdariTable') {
            if ($this->hasChild($item)) {
                foreach ($this->getChilds($item) as $child) {
                    $res = array_merge($res, $this->tree2flat($child, $acc));
                }
                return array_merge($res, $res);
            } else {
                $items = $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
                    'bid' => $acc['bid'],
                    'ref' => $item
                ], $acc['money']);
                $res = array_merge($res, $items);
            }
        } elseif ($this->getEntityName($item) == 'App\Entity\BankAccount') {
            $res = $this->getAllBanksRows($item, $acc);
        } elseif ($this->getEntityName($item) == 'App\Entity\Cashdesk') {
            $res = $this->getAllCashdeskRows($item, $acc);
        } elseif ($this->getEntityName($item) == 'App\Entity\Salary') {
            $res = $this->getAllSalarysRows($item, $acc);
        } elseif ($this->getEntityName($item) == 'App\Entity\Person') {
            $res = $this->getAllPersonsRows($item, $acc);
        } elseif ($this->getEntityName($item) == 'App\Entity\Commodity') {
            $res = $this->getAllCommoditiesRows($item, $acc);
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


    private function calculateOutput(array $items, HesabdariTable $item, array $acc): array
    {
        $res = [
            'his_bd' => 0,
            'his_bs' => 0,
            'bal_bd' => 0,
            'bal_bs' => 0,
            'id' => $item->getId(),
            'account' => $item->getName(),
            'type' => $item->getType(),
            'code' => $item->getCode(),
            'name' => $item->getName(),
            'isObject' => false,
            'hasChild' => $this->hasChild($item, $acc),
            'upperID' => $item->getUpper()->getId()
        ];
        foreach ($items as $item) {
            $res['his_bs'] += $item->getBs();
            $res['his_bd'] += $item->getBd();
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

    private function calculateOutputObject(array $items, HesabdariTable $node, array $acc, BankAccount|Person|Commodity|Cashdesk|Salary $obj): array
    {
        $res = [
            'his_bd' => 0,
            'his_bs' => 0,
            'bal_bd' => 0,
            'bal_bs' => 0,
            'id' => $obj->getId(),
            'account' => $obj->getName(),
            'type' => $node->getType(),
            'isObject' => true,
            'code' => $obj->getCode(),
            'name' => $obj->getName(),
            'hasChild' => false,
            'upperID' => $node->getId()
        ];
        foreach ($items as $item) {
            $res['his_bs'] += $item->getBs();
            $res['his_bd'] += $item->getBd();
        }
        if ($res['his_bd'] > $res['his_bs']) {
            $res['bal_bd'] = $res['his_bd'] - $res['his_bs'];
            $res['bal_bs'] = 0;
        } else {
            $res['bal_bs'] = $res['his_bs'] - $res['his_bd'];
            $res['bal_bd'] = 0;
        }
        if ($node->getType() == 'person') {
            $res['name'] = $obj->getNikename();
            $res['account'] = $obj->getNikename();
        }
        return $res;
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

    private function hasChild(HesabdariTable $table, array $acc = []): bool
    {
        if ($this->em->getRepository(HesabdariTable::class)->findOneBy(['upper' => $table]))
            return true;
        return false;
    }

    private function getChilds(HesabdariTable $table, array $acc = []): array
    {
        return $this->em->getRepository(HesabdariTable::class)->findBy(['upper' => $table]);
    }

    private function getAllBanksRows(HesabdariTable $table, array $acc, array $items = []): array
    {
        if (count($items) == 0) {
            $items = $this->em->getRepository(BankAccount::class)->findBy([
                'bid' => $acc['bid'],
                'money' => $acc['money'],
            ]);
        }
        return $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
            'bank' => $items,
            'year' => $acc['year'],
            'ref' => $table
        ], $acc['money']);
    }
    private function getAllCashdeskRows(HesabdariTable $table, array $acc, array $items = []): array
    {
        if (count($items) == 0) {
            $items = $this->em->getRepository(Cashdesk::class)->findBy([
                'bid' => $acc['bid'],
                'money' => $acc['money'],
            ]);
        }
        return $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
            'cashdesk' => $items,
            'year' => $acc['year'],
            'ref' => $table
        ], $acc['money']);
    }

    private function getAllSalarysRows(HesabdariTable $table, array $acc, array $items = []): array
    {
        if (count($items) == 0) {
            $items = $this->em->getRepository(Salary::class)->findBy([
                'bid' => $acc['bid'],
                'money' => $acc['money'],
            ]);
        }
        return $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
            'salary' => $items,
            'year' => $acc['year'],
            'ref' => $table
        ], $acc['money']);
    }
    private function getAllPersonsRows(HesabdariTable $table, array $acc, array $items = []): array
    {
        if (count($items) == 0) {
            $items = $this->em->getRepository(Person::class)->findBy([
                'bid' => $acc['bid'],
                'money' => $acc['money'],
            ]);
        }
        return $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
            'person' => $items,
            'year' => $acc['year'],
            'ref' => $table
        ], $acc['money']);
    }

    private function getAllCommoditiesRows(HesabdariTable $table, array $acc, array $items = []): array
    {
        if (count($items) == 0) {
            $items = $this->em->getRepository(Commodity::class)->findBy([
                'bid' => $acc['bid'],
                'money' => $acc['money'],
            ]);
        }
        return $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
            'commodity' => $items,
            'year' => $acc['year'],
            'ref' => $table
        ], $acc['money']);
    }

}
