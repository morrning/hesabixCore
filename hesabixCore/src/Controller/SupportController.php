<?php

namespace App\Controller;

use App\Entity\Support;
use App\Service\Jdate;
use App\Service\SMS;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class SupportController extends AbstractController
{
    #[Route('/api/support/list', name: 'app_support_list')]
    public function app_support_list(Jdate $jdate,EntityManagerInterface $entityManager): JsonResponse
    {
        $items = $entityManager->getRepository(Support::class)->findBy([
            'submitter'=>$this->getUser(),
            'main'=>0
        ],
        [
            'id'=>'DESC'
        ]);
        foreach ($items as $item){
            $item->setDateSubmit($jdate->jdate('Y/n/d H:i',$item->getDateSubmit()));
        }
        return $this->json($items);
    }

    #[Route('/api/support/mod/{id}', name: 'app_support_mod')]
    public function app_support_mod(SMS $SMS,Request $request, EntityManagerInterface $entityManager,string $id = ''): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if($id == ''){
            if(array_key_exists('title',$params) && array_key_exists('body',$params)){
                $item  = new Support();
                $item->setBody($params['body']);
                $item->setTitle($params['title']);
                $item->setDateSubmit(time());
                $item->setSubmitter($this->getUser());
                $item->setMain(0);
                $item->setState('در حال پیگیری');
                $entityManager->persist($item);
                $entityManager->flush();
                //send sms to manager
                $SMS->send([$item->getId()],'162214','09183282405');
                return $this->json([
                    'error'=> 0,
                    'message'=> 'ok',
                    'url'=>$item->getId()
                ]);
            }
        }
        else{
            if(array_key_exists('body',$params) ){
                $item  = new Support();
                $upper = $entityManager->getRepository(Support::class)->find($id);
                if($upper)
                    $item->setMain($upper->getid());
                $item->setBody($params['body']);
                $item->setTitle($upper->getTitle());
                $item->setDateSubmit(time());
                $item->setSubmitter($this->getUser());
                $item->setState('در حال پیگیری');
                $entityManager->persist($item);
                $entityManager->flush();
                $upper->setState('در حال پیگیری');
                $entityManager->persist($upper);
                $entityManager->flush();
                //send sms to manager
                $SMS->send([$item->getId()],'162214','09183282405');
                return $this->json([
                    'error'=> 0,
                    'message'=> 'ok',
                    'url'=>$item->getId()
                ]);
            }
        }

        return $this->json([
            'error'=> 999,
            'message'=> 'تمام موارد لازم را وارد کنید.'
        ]);
    }

    #[Route('/api/support/view/{id}', name: 'app_support_view')]
    public function app_support_view(Jdate $jdate, EntityManagerInterface $entityManager,string $id = ''): JsonResponse
    {
        $item = $entityManager->getRepository(Support::class)->find($id);
        if(!$item) throw $this->createNotFoundException();
        if($item->getSubmitter() != $this->getUser()) throw $this->createAccessDeniedException();
        $replays = $entityManager->getRepository(Support::class)->findBy(['main'=>$item->getId()]);
        foreach ($replays as $replay){
            $replay->setDateSubmit($jdate->jdate('Y/n/d H:i',$replay->getDateSubmit()));
            $replay->setTitle($replay->getSubmitter()->getFullname());
            if($replay->getSubmitter() == $this->getUser())
                $replay->setState(1);
            else
                $replay->setState(0);
        }
        $item->setDateSubmit($jdate->jdate('Y/n/d H:i',$item->getDateSubmit()));
        return $this->json([
            'item'=> $item,
            'replays'=> $replays
        ]);
    }
}
