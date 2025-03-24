<?php

namespace App\Controller;

use App\Entity\Business;
use App\Service\Access;
use App\Service\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class AvatarController extends AbstractController
{
    #[Route('/front/avatar/file/get/{id}', name: 'front_avatar_file_get')]
    public function front_avatar_file_get(EntityManagerInterface $entityManager, string $id = '0'): BinaryFileResponse
    {
        $bid = $entityManager->getRepository(Business::class)->find($id);
        if (!$bid)
            return new BinaryFileResponse(dirname(__DIR__, 3) . '/hesabixArchive/avatars/default.png');
        $fileAdr = dirname(__DIR__, 3) . '/hesabixArchive/avatars/' . $bid->getAvatar();
        if (!$bid->getAvatar() || !file_exists($fileAdr))
            return new BinaryFileResponse(dirname(__DIR__, 3) . '/hesabixArchive/avatars/default.png');
        $response = new BinaryFileResponse($fileAdr);
        return $response;
    }

    #[Route('/api/avatar/get', name: 'api_avatar_get')]
    public function api_avatar_get(Access $access): Response
    {
        $acc = $access->hasRole('settings');
        if (!$acc)
            throw $this->createAccessDeniedException();
        if ($acc['bid']->getAvatar()) {
            return new Response($acc['bid']->getAvatar());
        }
        return new Response('default.png');
    }

    #[Route('/api/seal/get', name: 'api_seal_get')]
    public function api_seal_get(Access $access): Response
    {
        $acc = $access->hasRole('settings');
        if (!$acc)
            throw $this->createAccessDeniedException();
        if ($acc['bid']->getSealFile()) {
            return new Response($acc['bid']->getSealFile());
        }
        return new Response('default.png');
    }

    #[Route('/api/avatar/get/file/{id}', name: 'api_avatar_get_file')]
    public function api_avatar_get_file(string $id = ''): BinaryFileResponse
    {
        $fileAdr = __DIR__ . '/../../../hesabixArchive/avatars/' . $id;
        if (!file_exists($fileAdr))
            $fileAdr = __DIR__ . '/../../../hesabixArchive/avatars/default.png';
        $response = new BinaryFileResponse($fileAdr);
        return $response;
    }

    #[Route('/front/seal/file/get/{id}', name: 'front_seal_file_get')]
    public function front_seal_file_get(EntityManagerInterface $entityManager, string $id = '0'): BinaryFileResponse
    {
        $bid = $entityManager->getRepository(Business::class)->find($id);
        if (!$bid)
            return new BinaryFileResponse(dirname(__DIR__, 3) . '/hesabixArchive/seal/default.png');
        $fileAdr = dirname(__DIR__, 3) . '/hesabixArchive/seal/' . $bid->getSealFile();
        if (!$bid->getAvatar() || !file_exists($fileAdr))
            return new BinaryFileResponse(dirname(__DIR__, 3) . '/hesabixArchive/seal/default.png');
        $response = new BinaryFileResponse($fileAdr);
        return $response;
    }


    #[Route('/api/seal/get/file/{id}', name: 'api_seal_get_file')]
    public function api_seal_get_file(string $id = null): BinaryFileResponse
    {
        if ($id) {
            $fileAdr = __DIR__ . '/../../../hesabixArchive/seal/' . $id;
            if (!file_exists($fileAdr))
                throw $this->createNotFoundException();
            $response = new BinaryFileResponse($fileAdr);
            return $response;
        }
        $fileAdr = __DIR__ . '/../../../hesabixArchive/seal/default.png';
        if (!file_exists($fileAdr))
            throw $this->createNotFoundException();
        $response = new BinaryFileResponse($fileAdr);
        return $response;

    }

    #[Route('/api/avatar/post', name: 'api_avatar_post')]
    public function api_avatar_post(Log $log, SluggerInterface $slugger, Request $request, Access $access, EntityManagerInterface $entityManagerInterface): Response
    {
        $acc = $access->hasRole('owner');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $uploadedFile = $request->files->get('bytes');
        if ($uploadedFile) {
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
            $ext = $uploadedFile->getClientOriginalExtension();
            $extOK = false;
            if ($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg') {
                $extOK = true;
            } else {
                return new Response('e');
            }
            $sizeOK = false;
            if ($uploadedFile->getSize() < 1000000) {
                $sizeOK = true;
            } else {
                return new Response('s');
            }
            $imgSizeOK = false;
            $info = getimagesize($uploadedFile);
            list($x, $y) = $info;
            if ($x < 513 && $y < 513) {
                $imgSizeOK = true;
            } else {
                return new Response('is');
            }
            if ($extOK && $sizeOK && $imgSizeOK) {
                // Move the file to the directory where brochures are stored
                try {
                    $uploadedFile->move(
                        $this->getParameter('avatarDir'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    return $this->json("error");
                }
                $acc['bid']->setAvatar($newFilename);
                $entityManagerInterface->persist($acc['bid']);
                $entityManagerInterface->flush();
                //save log
                $log->insert('تنظیمات پایه', 'نمایه کسب و کار تغییر یافت', $this->getUser(), $acc['bid']);

                return new Response($acc['bid']->getAvatar());
            }
        }

        if ($acc['bid']->getAvatar()) {
            return new Response($acc['bid']->getAvatar());
        }
        return new Response('default.png');
    }

    #[Route('/api/seal/post', name: 'api_seal_post')]
    public function api_seal_post(Log $log, SluggerInterface $slugger, Request $request, Access $access, EntityManagerInterface $entityManagerInterface): Response
    {
        $acc = $access->hasRole('owner');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $uploadedFile = $request->files->get('bytes');
        if ($uploadedFile) {
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
            $ext = $uploadedFile->getClientOriginalExtension();
            $extOK = false;
            if ($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg') {
                $extOK = true;
            } else {
                return new Response('e');
            }
            $sizeOK = false;
            if ($uploadedFile->getSize() < 1000000) {
                $sizeOK = true;
            } else {
                return new Response('s');
            }
            $imgSizeOK = false;
            $info = getimagesize($uploadedFile);
            list($x, $y) = $info;
            if ($x < 513 && $y < 513) {
                $imgSizeOK = true;
            } else {
                return new Response('is');
            }
            if ($extOK && $sizeOK && $imgSizeOK) {
                // Move the file to the directory where brochures are stored
                try {
                    $uploadedFile->move(
                        $this->getParameter('sealDir'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    return $this->json("error");
                }
                $acc['bid']->setSealFile($newFilename);
                $entityManagerInterface->persist($acc['bid']);
                $entityManagerInterface->flush();
                //save log
                $log->insert('تنظیمات پایه', 'مهر کسب و کار تغییر یافت', $this->getUser(), $acc['bid']);

                return new Response($acc['bid']->getAvatar());
            }
        }

        if ($acc['bid']->getSealFile()) {
            return new Response($acc['bid']->getSealFile());
        }
        return new Response('default.png');
    }
}
