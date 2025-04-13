<?php

namespace App\Controller\System;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Statment;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class StatementController extends AbstractController
{

    #[Route('/api/general/statements', name: 'general_statement')]
    public function general_statement(EntityManagerInterface $entityManager): JsonResponse
    {
        return $this->json($entityManager->getRepository(Statment::class)->findBy([], ['id' => 'DESC']));
    }

    #[Route('/api/admin/statement/list', name: 'system_statement_admin_list')]
    public function admin_statement_list(EntityManagerInterface $entityManager): JsonResponse
    {
        return $this->json($entityManager->getRepository(Statment::class)->findBy([], ['id' => 'DESC']));
    }

    #[Route('/api/admin/statement/mod', name: 'system_statement_admin_create', methods: ['POST'])]
    public function admin_statement_create(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $statement = new Statment();
        
        $statement->setTitle($data['title'] ?? '');
        $statement->setBody($data['body'] ?? '');
        $statement->setDateSubmit($data['dateSubmit'] ?? date('Y-m-d'));

        $entityManager->persist($statement);
        $entityManager->flush();

        return $this->json(['success' => true, 'id' => $statement->getId()]);
    }

    #[Route('/api/admin/statement/mod/{id}', name: 'system_statement_admin_mod', methods: ['GET', 'PUT', 'DELETE'])]
    public function admin_statement_mod(
        EntityManagerInterface $entityManager,
        Request $request,
        ?string $id = null
    ): JsonResponse {
        if (!$id) {
            return $this->json(['error' => 'ID is required'], 400);
        }

        $statement = $entityManager->getRepository(Statment::class)->find($id);
        if (!$statement) {
            return $this->json(['error' => 'Statement not found'], 404);
        }

        if ($request->isMethod('GET')) {
            return $this->json($statement);
        }

        if ($request->isMethod('DELETE')) {
            $entityManager->remove($statement);
            $entityManager->flush();
            return $this->json(['success' => true]);
        }

        if ($request->isMethod('PUT')) {
            $data = json_decode($request->getContent(), true);
            $statement->setTitle($data['title'] ?? '');
            $statement->setBody($data['body'] ?? '');
            $statement->setDateSubmit($data['dateSubmit'] ?? date('Y-m-d'));
            $entityManager->flush();
            return $this->json(['success' => true]);
        }

        return $this->json(['error' => 'Method not allowed'], 405);
    }
}
