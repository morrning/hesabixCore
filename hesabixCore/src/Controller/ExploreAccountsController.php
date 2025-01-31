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
use App\Service\Extractor;
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
        $childs = $this->getChilds($node, $acc);
        $output = [];
        foreach ($childs as $child) {
            $childRows = $this->tree2flat($child, $acc);
            $temp = [
                'id' => $child->getId(),
                'code' => $child->getCode(),
                'name' => $child->getName(),
            ];
            $temp = array_merge($temp, $this->calculateOutput($childRows, $child));
            $output[] = $temp;
        }
        $data = [];
        $data['itemData'] = $output;
        $data['tree'] = $this->getTree($node);
        return $this->json($data);
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


    private function calculateOutput(array $items, HesabdariTable $item): array
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
}
