<?php

namespace App\Controller;

use App\Entity\Settings;
use App\Entity\Support;
use App\Entity\User;
use App\Service\Jdate;
use App\Service\Notification;
use App\Service\Provider;
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
    #[Route('/api/admin/support/list', name: 'app_admin_support_list')]
    public function app_admin_support_list(Provider $provider,Jdate $jdate,EntityManagerInterface $entityManager): JsonResponse
    {
        $items = $entityManager->getRepository(Support::class)->findBy(['main' => 0],['id'=>'DESC']);
        foreach ($items as $item){
            $item->setDateSubmit($jdate->jdate('Y/n/d H:i',$item->getDateSubmit()));
        }
        return $this->json($provider->ArrayEntity2Array($items,1));
    }
    #[Route('/api/admin/support/view/{id}', name: 'app_admin_support_view')]
    public function app_admin_support_view(Jdate $jdate, EntityManagerInterface $entityManager,string $id = ''): JsonResponse
    {
        $item = $entityManager->getRepository(Support::class)->find($id);
        if(!$item) throw $this->createNotFoundException();
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
    #[Route('/api/admin/support/mod/{id}', name: 'app_admin_support_mod')]
    public function app_admin_support_mod(SMS $SMS,Request $request, EntityManagerInterface $entityManager,Notification $notifi,string $id = ''): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        $item = $entityManager->getRepository(Support::class)->find($id);
        if(!$item) $this->createNotFoundException();
        if(array_key_exists('body',$params)){
            $support = new Support();
            $support->setDateSubmit(time());
            $support->setTitle('0');
            $support->setBody($params['body']);
            $support->setState('0');
            $support->setMain($item->getId());
            $support->setSubmitter($this->getUser());
            $entityManager->persist($support);
            $entityManager->flush();
            $item->setState('پاسخ داده شده');
            $entityManager->persist($support);
            $entityManager->flush();
            //send sms to customer
            if($item->getSubmitter()->getMobile())
                $SMS->send([$item->getId()],'162251',$item->getSubmitter()->getMobile());
            //send notification to user
            $settings = $entityManager->getRepository(Settings::class)->findAll()[0];
            $url = $settings->getAppSite() . '/profile/support-view/' . $item->getId();
            $notifi->insert("به درخواست پشتیبانی پاسخ داده شد",$url,null,$item->getSubmitter());
            return $this->json([
                'error'=> 0,
                'message'=> 'successful'
            ]);
        }
        return $this->json([
            'error'=> 999,
            'message'=> 'تمام موارد لازم را وارد کنید.'
        ]);
    }
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
                $admins = $entityManager->getRepository(User::class)->findByRole('ROLE_ADMIN');
                foreach($admins as $admin)
                    $SMS->send([$item->getId()],'162214',$admin->getMobile());
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
