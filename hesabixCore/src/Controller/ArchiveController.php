<?php

namespace App\Controller;

use App\Entity\ArchiveFile;
use App\Service\Access;
use App\Service\Jdate;
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

    #[Route('/api/archive/list/{cat}', name: 'app_archive_list')]
    public function app_archive_list(string $cat,Jdate $jdate,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,$code = 0): JsonResponse
    {
        $acc = $access->hasRole('archiveUpload');
        if(!$acc)
            $acc = $access->hasRole('archiveMod');
        if(!$acc)
            $acc = $access->hasRole('archiveDelete');
        if(!$acc)
            throw $this->createAccessDeniedException();
        if($cat == 'all')
            $files = $entityManager->getRepository(ArchiveFile::class)->findBy(['bid'=>$acc['bid']]);
        else
            $files = $entityManager->getRepository(ArchiveFile::class)->findBy(['bid'=>$acc['bid'],'cat'=>$cat]);
        $resp = [];
        foreach ($files as $file){
            $temp = [];
            $temp['filename']=$file->getFilename();
            $temp['fileType']=$file->getFileType();
            $temp['submitter']=$file->getSubmitter()->getFullName();
            $temp['dateSubmit']=$jdate->jdate('Y/n/d H:i',$file->getDateSubmit());
            $temp['filePublicls']=$file->isPublic();
            $temp['cat']=$file->getCat();
            $temp['filesize']=$file->getFileSize();
            $resp[] = $temp;
        }

        return $this->json($resp);
    }
}
