<?php

namespace App\Controller;

use App\Service\Log;
use App\Service\Jdate;
use App\Entity\MostDes;
use App\Service\Access;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class MostdesController extends AbstractController
{
    #[Route('/api/mostdes/list', name: 'api_mostdes_list')]
    public function api_mostdes_list(Request $request, Access $access, Jdate $jdate, EntityManagerInterface $entityManager, Log $log): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        $items = $entityManager->getRepository(MostDes::class)->findBy([
            'bid' => $acc['bid'],
            'submitter' => $this->getUser(),
            'type' => $params['type']
        ]);
        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'id'=>$item->getId(),
                'des'=>$item->getDes()
            ];
        }
        return $this->json($result);
    }

    #[Route('/api/mostdes/add', name: 'api_mostdes_add')]
    public function api_mostdes_add(Request $request, Access $access, Jdate $jdate, EntityManagerInterface $entityManager, Log $log): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        $des = new MostDes();
        $des->setBid($acc['bid']);
        $des->setSubmitter($this->getUser());
        $des->setType($params['type']);
        $des->setDes($params['des']);
        $entityManager->persist($des);
        $entityManager->flush();
        return $this->json(['result' => 1]);
    }
    #[Route('/api/mostdes/remove/{id}', name: 'api_mostdes_remove')]
    public function api_mostdes_remove(string $id, Request $request, Access $access, Jdate $jdate, EntityManagerInterface $entityManager, Log $log): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $item = $entityManager->getRepository(MostDes::class)->findOneBy([
            'bid' => $acc['bid'],
            'submitter' => $this->getUser(),
            'id' => $id
        ]);
        $entityManager->remove($item);
        $entityManager->flush();
        return $this->json(['result' => 1]);
    }
}
