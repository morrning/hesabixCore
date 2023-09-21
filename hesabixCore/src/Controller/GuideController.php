<?php

namespace App\Controller;

use App\Entity\GuideContent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GuideController extends AbstractController
{
    #[Route('/api/guide/get/list/{id}', name: 'app_guide_get_list')]
    public function app_guide_get_list($id,EntityManagerInterface $entityManager): JsonResponse
    {
        $lists = $entityManager->getRepository(GuideContent::class)->findBy(['cat'=>$id]);
        $res=[];
        foreach($lists as $list){
            $tmp = [];
            $tmp['title'] = $list->getTitle();
            $tmp['url'] = $list->getUrl();
            $res[] = $tmp;
        }
        return $this->json($res);
    }
    #[Route('/api/guide/get/content/{id}', name: 'app_guide_get_content')]
    public function app_guide_get_content($id,EntityManagerInterface $entityManager): JsonResponse
    {
        $content = $entityManager->getRepository(GuideContent::class)->findOneBy(['url'=>$id]);
        return $this->json([
            'title'=> $content->getTitle(),
            'body'=> $content->getBody(),
            'url'=>$content->getUrl(),
            'cat'=>$content->getCat()
        ]);
    }

    #[Route('/api/guide/update/content/{id}', name: 'app_guide_update_content')]
    public function app_guide_update_content($id,Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $guide = $entityManager->getRepository(GuideContent::class)->findOneBy(['url'=>$id]);
        if(is_null($guide))
            throw $this->createNotFoundException();

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(array_key_exists('title',$params) && array_key_exists('cat',$params) && array_key_exists('body',$params  )){
            if($entityManager->getRepository(GuideContent::class)->findOneBy(['url'=>$params['url']]) && $params['url'] != $id){
                return $this->json([
                    'error'=> 1,
                    'message'=> 'این پیوند قبلا ثبت شده است.'
                ]);
            }

            $guide->setBody($params['body']);
            $guide->setCat($params['cat']);
            $guide->setTitle($params['title']);
            $guide->setDateSubmit(time());
            $guide->setSubmiter($this->getUser());
            if($id != 'home')
                $guide->setUrl(str_replace(' ','_',$params['url']));
            $entityManager->persist($guide);
            $entityManager->flush();
            return $this->json([
                'error'=> 0,
                'message'=> 'ok',
                'url'=>$guide->getUrl()
            ]);
        }
        return $this->json([
            'error'=> 999,
            'message'=> 'تمام موارد لازم را وارد کنید.'
        ]);
    }

    #[Route('/api/guide/insert', name: 'app_guide_insert')]
    public function app_guide_insert(Request $request,EntityManagerInterface $entityManager): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(array_key_exists('title',$params) && array_key_exists('cat',$params) && array_key_exists('body',$params  )){
            if($entityManager->getRepository(GuideContent::class)->findOneBy(['url'=>$params['url']])){
                return $this->json([
                    'error'=> 1,
                    'message'=> 'این پیوند قبلا ثبت شده است.'
                ]);
            }
            $guide = new GuideContent();
            $guide->setBody($params['body']);
            $guide->setCat($params['cat']);
            $guide->setTitle($params['title']);
            $guide->setDateSubmit(time());
            $guide->setSubmiter($this->getUser());
            $guide->setUrl(str_replace(' ','_',$params['url']));
            $entityManager->persist($guide);
            $entityManager->flush();
            return $this->json([
                'error'=> 0,
                'message'=> 'ok',
                'url'=>$guide->getUrl()
            ]);
        }
        return $this->json([
            'error'=> 999,
            'message'=> 'تمام موارد لازم را وارد کنید.'
        ]);
    }
    #[Route('/api/guide/delete/{id}', name: 'app_guide_delete')]
    public function app_guide_delete($id,Request $request,EntityManagerInterface $entityManager): JsonResponse
    {
        $content = $entityManager->getRepository(GuideContent::class)->findOneBy(['url'=>$id]);
        if($content && $content->getUrl() != 'home')
            $entityManager->getRepository(GuideContent::class)->remove($content,true);
        return $this->json([
            'error'=> 0,
            'message'=> 'ok',
        ]);
    }
}
