<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Entity\Cashdesk;
use App\Entity\HesabdariRow;
use App\Entity\HesabdariTable;
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

        // if($params['node'] == 'root'){
        //     $params['node'] = $entityManagerInterface
        //     ->getRepository(HesabdariTable::class)
        //     ->findOneBy(['upper'=>null])
        //     ->getId();
        // }
        if ($params['node'] == 'root') {
            $params['node'] = 9;
        }

        $node = $entityManagerInterface->getRepository(HesabdariTable::class)->findNode($params['node'], $acc['bid']->getId());
        if (!$node)
            throw $this->createNotFoundException();

        if ($node->getType() == 'bank') {
            $rows = $this->getAllBanksRows($node, $acc);
        } elseif ($node->getType() == 'cashdesk') {

        } elseif ($node->getType() == 'salary') {

        } elseif ($node->getType() == 'calc') {
            echo 33;
        } elseif ($node->getType() == 'person') {

        } elseif ($node->getType() == 'commodity') {

        } elseif ($node->getType() == 'cheque') {

        }
        return $this->json([]);
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

    private function getAllBanksRows(HesabdariTable $table, array $acc): array
    {
        $items = $this->em->getRepository(BankAccount::class)->findBy([
            'bid' => $acc['bid'],
            'money' => $acc['money'],
        ]);
        return $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
            'bank' => $items,
            'year' => $acc['year'],
            'ref' => $table
        ], $acc['money']);
    }
    private function getAllCashdeskRows(HesabdariTable $table, array $acc): array
    {
        $items = $this->em->getRepository(Cashdesk::class)->findBy([
            'bid' => $acc['bid'],
            'money' => $acc['money'],
        ]);
        return $this->em->getRepository(HesabdariRow::class)->findByJoinMoney([
            'cashdesk' => $items,
            'year' => $acc['year'],
            'ref' => $table
        ], $acc['money']);
    }
}
