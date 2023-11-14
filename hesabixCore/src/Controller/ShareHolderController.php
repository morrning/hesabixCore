<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\Shareholder;
use App\Service\Access;
use App\Service\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShareHolderController extends AbstractController
{
    #[Route('/api/shareholders/list', name: 'app_shareholders_list')]
    public function app_shareholders_list(Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        if(!$access->hasRole('shareholder'))
            throw $this->createAccessDeniedException();
        $datas = $entityManager->getRepository(Shareholder::class)->findBy([
            'bid'=>$request->headers->get('activeBid')
        ]);
        $resp = [];
        foreach($datas as $data){
            $temp = [];
            $temp['id']= $data->getId();
            $temp['person'] = ['id'=>$data->getPerson()->getId(),'nikename'=>$data->getPerson()->getNikename()];
            $temp['percent'] = $data->getPercent();
            $resp[] = $temp;
        }
        return $this->json($resp);
    }
    #[Route('/api/shareholders/insert', name: 'app_shareholders_insert')]
    public function app_shareholders_insert(Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('shareholder');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(!array_key_exists('person',$params) && !array_key_exists('count',$params))
            return $this->json(['result'=>-1]);
        $person = $entityManager->getRepository(Person::class)->find($params['person']);
        if($person){
            if($person->getBid()->getId() != $acc['bid']->getId()){
                throw $this->createAccessDeniedException();
            }
            else {
                $item = $entityManager->getRepository(Shareholder::class)->findOneBy(['person'=>$person]);
                if($item){
                    $item->setPercent($item->getPercent() + (int)$params['count']);
                }
                else{
                    $item = new Shareholder();
                    $item->setBid($acc['bid']);
                    $item->setPerson($person);
                    $item->setPercent((int)$params['count']);
                }
                $entityManager->persist($item);
                $entityManager->flush();
                $log->insert('سهامداران','تعداد ' . $params['count'] . ' سهم به نام ' . $person->getName() . ' افزوده/ویرایش شد.',$this->getUser(),$acc['bid']);
                return $this->json(['result'=>1]);
            }
        }
        throw $this->createNotFoundException();
    }

    #[Route('/api/shareholders/remove/{id}', name: 'app_shareholders_remove')]
    public function app_shareholders_remove(string $id,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('shareholder');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $shareholder = $entityManager->getRepository(Shareholder::class)->find($id);
        if($shareholder){
            if($shareholder->getBid()->getId() == $acc['bid']->getId()){
                $log->insert('سهامداران','سهامدار با نام  ' . $shareholder->getPerson()->getNikename() . ' حذف شد. ',$this->getUser(),$acc['bid']);
                $entityManager->remove($shareholder);
                $entityManager->flush();
                return $this->json(['result'=>1]);
            }
        }
        throw $this->createNotFoundException();
    }
}
