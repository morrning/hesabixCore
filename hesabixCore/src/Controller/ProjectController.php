<?php

namespace App\Controller;

use App\Entity\Project;
use App\Service\Access;
use App\Service\Explore;
use App\Service\Extractor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProjectController extends AbstractController
{
    #[Route('/api/projects/list', name: 'api_project_list')]
    public function api_project_list(Extractor $extractor,EntityManagerInterface $entityManagerInterface, Access $access): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $projects = $entityManagerInterface->getRepository(Project::class)->findBy([
            'bid' => $acc['bid']
        ]);
        return $this->json(
            $extractor->operationSuccess(Explore::ExploreProjects($projects))
        );
    }
}
