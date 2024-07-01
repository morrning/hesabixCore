<?php

namespace App\Controller;

use App\Service\Access;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AvatarController extends AbstractController
{
    #[Route('/api/avatar/get', name: 'api_avatar_get')]
    public function api_avatar_get(Access $access): Response
    {
        $acc = $access->hasRole('owner');
        if (!$acc) throw $this->createAccessDeniedException();
        if ($acc['bid']->getAvatar()) {
            return new Response($acc['bid']->getAvatar());
        }
        return new Response('default.png');
    }

    #[Route('/api/avatar/get/file/{id}', name: 'api_avatar_get_file')]
    public function api_avatar_get_file(string $id): BinaryFileResponse
    {
        $fileAdr = __DIR__ . '/../../../hesabixArchive/avatars/' . $id;
        if(!file_exists($fileAdr))
            throw $this->createNotFoundException();
        $response = new BinaryFileResponse($fileAdr);
        return $response;
    }

    #[Route('/api/avatar/post', name: 'api_avatar_post')]
    public function api_avatar_post(Access $access,EntityManagerInterface $entityManagerInterface): Response
    {
        $acc = $access->hasRole('owner');
        if (!$acc) throw $this->createAccessDeniedException();
        var_dump($params);
        if ($acc['bid']->getAvatar()) {
            return new Response($acc['bid']->getAvatar());
        }
        return new Response('default.png');
    }
}
