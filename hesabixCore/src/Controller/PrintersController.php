<?php

namespace App\Controller;

use App\Entity\Printer;
use App\Service\Access;
use App\Service\Extractor;
use App\Service\Log;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PrintersController extends AbstractController
{

    /**
     * function to generate random strings
     * @param 		int 	$length 	number of characters in the generated string
     * @return 		string	a new string is created with random characters of the desired length
     */
    private function RandomString($length = 32)
    {
        return substr(str_shuffle(str_repeat($x = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }


    #[Route('/api/printers/list', name: 'app_printers_list')]
    public function app_printers_list(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('owner');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $items = $entityManager->getRepository(Printer::class)->findBy(['bid' => $acc['bid']]);
        $res = [];
        foreach ($items as $item) {
            $temp = [];
            $temp['id'] = $item->getId();
            $temp['name'] = $item->getName();
            $temp['token'] = $item->getToken();
            $temp['status'] = 'معتبر';
            $res[] = $temp;
        }
        return $this->json($res);
    }
    #[Route('/api/printers/delete/{code}', name: 'app_printers_delete')]
    public function app_printers_delete(Provider $provider, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager, $code = 0): JsonResponse
    {
        $acc = $access->hasRole('owner');
        if (!$acc)
            throw $this->createAccessDeniedException();

        $printer = $entityManager->getRepository(Printer::class)->findOneBy(['bid' => $acc['bid'], 'id' => $code]);
        if (!$printer)
            throw $this->createNotFoundException();
        //check accounting docs

        $comName = $printer->getName();
        $entityManager->remove($printer);
        $log->insert('چاپگر‌های ابری', '  چاپگر  با نام ' . $comName . ' حذف شد. ', $this->getUser(), $acc['bid']->getId());
        return $this->json(['result' => 1]);
    }

    #[Route('/api/printers/insert', name: 'app_printers_insert')]
    public function app_printers_insert(Extractor $extractor, Request $request, Access $access, Log $log, EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('owner');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('name', $params))
            return $this->json($extractor->paramsNotSend());
        $item =  $entityManager->getRepository(Printer::class)->findOneBy(['bid' => $acc['bid'], 'name' => $params['name']]);
        if($item)
            return $this->json(['result'=>2]);
        $item = new Printer();
        $item->setBid($acc['bid']);
        $item->setName($params['name']);
        $item->setToken($this->RandomString(42));
        $entityManager->persist($item);
        $entityManager->flush();
        return $this->json([
            'result' => 1
        ]);
    }
}
