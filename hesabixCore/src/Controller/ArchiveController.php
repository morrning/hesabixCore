<?php

namespace App\Controller;

use App\Service\Access;
use App\Service\Log;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArchiveController extends AbstractController
{
    #[Route('/api/archive/info', name: 'app_archive_info')]
    public function app_archive_info(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,$code = 0): JsonResponse
    {
        $acc = $access->hasRole('archiveInfo');
        if(!$acc)
            throw $this->createAccessDeniedException();
        return $this->json([
           'size' => $acc['bid']->getArchiveSize()
        ]);
    }
}
