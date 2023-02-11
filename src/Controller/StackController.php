<?php

namespace App\Controller;

use App\Entity\StackCat;
use App\Entity\StackContent;
use App\Service\Jdate;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class StackController extends AbstractController
{
    #[Route('/api/stack/cats/get', name: 'app_stack_get_cats')]
    public function app_stack_get_cats(SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $result = [];
        $cats = $entityManager->getRepository(StackCat::class)->findAll();
        foreach ($cats as $cat){
            $temp = [];
            $temp['id'] = $cat->getId();
            $temp['name'] = $cat->getName();
            $temp['code'] = $cat->getCode();
            $result[] = $temp;
        }
        return $this->json($result);
    }
    #[Route('/api/stack/insert', name: 'app_stack_content_insert')]
    public function app_stack_content_insert(Request $request,SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(array_key_exists('title',$params) && array_key_exists('cat',$params) && array_key_exists('body',$params  )){
            $cat = $entityManager->getRepository(StackCat::class)->find($params['cat']);
            $stack = new StackContent();
            $stack->setBody($params['body']);
            $stack->setCat($cat);
            $stack->setTitle($params['title']);
            $stack->setDateSubmit(time());
            $stack->setViews(0);
            $stack->setSubmitter($this->getUser());
            $stack->setUrl(str_replace(' ','_',$params['title']));
            $entityManager->persist($stack);
            $entityManager->flush();
            return $this->json([
                'error'=> 0,
                'message'=> 'ok',
                'url'=>$stack->getUrl()
            ]);
        }
        return $this->json([
            'error'=> 999,
            'message'=> 'تمام موارد لازم را وارد کنید.'
        ]);
    }

    #[Route('/api/stack/replay/insert', name: 'app_stack_replay_insert')]
    public function app_stack_replay_insert(Jdate $jdate,Provider $provider,Request $request,SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if( array_key_exists('upper',$params) && array_key_exists('body',$params  )){
            $upper = $entityManager->getRepository(StackContent::class)->findOneBy(['url'=>$params['upper']]);
            if(!$upper)
                throw $this->createNotFoundException();
            $stack = new StackContent();
            $stack->setUpper($upper);
            $stack->setBody($params['body']);
            $stack->setCat($upper->getCat());
            $stack->setTitle('پاسخ به: ' . $upper->getTitle());
            $stack->setDateSubmit(time());
            $stack->setViews(0);
            $stack->setSubmitter($this->getUser());
            $stack->setUrl('0');
            $entityManager->persist($stack);
            $entityManager->flush();
            return $this->json([
                'error'=> 0,
                'message'=> 'ok',
                'url'=>$stack->getUrl(),
                'data'=>[
                    'id'=> $stack->getId(),
                    'title' => $stack->getTitle(),
                    'body' => $stack->getBody(),
                    'submitter' => $stack->getSubmitter()->getFullName(),
                    'dateSubmit' => $jdate->pastTime($stack->getDateSubmit()),
                    'cat' => $stack->getCat()->getName(),
                    'views' => $stack->getViews(),
                    'url' => $stack->getUrl(),
                    'submitter_gravatar_hash' => $provider->gravatarHash($stack->getSubmitter()->getEmail())
            ]
            ]);
        }
        return $this->json([
            'error'=> 999,
            'message'=> 'تمام موارد لازم را وارد کنید.',
        ]);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    #[Route('/api/stack/contents/search', name: 'app_stack_contents_get')]
    public function app_stack_contents_get(Jdate $jdate, Provider $provider,Request $request,SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        $params = $provider->createSearchParams($request);
        $items = $entityManager->getRepository(StackContent::class)->search($params);
        $response = [];
        foreach ($items as $item){
            $temp = [];
            $temp['id'] = $item->getId();
            $temp['title'] = $item->getTitle();
            $temp['body'] = $item->getBody();
            $temp['submitter'] = $item->getSubmitter()->getFullName();
            $temp['dateSubmit'] = $jdate->pastTime($item->getDateSubmit());
            $temp['cat'] = $item->getCat()->getName();
            $temp['views'] = $item->getViews();
            $temp['url'] = $item->getUrl();
            $temp['replaysCount'] = $entityManager->getRepository(StackContent::class)->getCountReplaysOfQuestion($item);
            $replays = $entityManager->getRepository(StackContent::class)->getReplaysOfQuestion($item);
            $temp['lastReplayPerson'] = '';
            $temp['lastReplayDate'] = '';
            if(count($replays) != 0){
                $temp['lastReplayPerson'] = $replays[0]->getSubmitter()->getFullName();
                $temp['lastReplayDate'] = $jdate->pastTime($replays[0]->getDateSubmit());
            }
            $response[] = $temp;
        }
        return $this->json($response);
    }

    #[Route('/api/stack/replays/search/{url}', name: 'app_stack_replays_get')]
    public function app_stack_replays_get($url,Jdate $jdate, Provider $provider,Request $request,SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $upper = $entityManager->getRepository(StackContent::class)->findOneBy(['url'=>$url]);
        if(!$upper)
            throw $this->createNotFoundException();
        $items = $entityManager->getRepository(StackContent::class)->findBy(
            [
                'upper'=>$upper,
            ]
        );
        $response = [];
        foreach ($items as $item){
            $temp = [];
            $temp['id'] = $item->getId();
            $temp['title'] = $item->getTitle();
            $temp['body'] = $item->getBody();
            $temp['submitter'] = $item->getSubmitter()->getFullName();
            $temp['dateSubmit'] = $jdate->pastTime($item->getDateSubmit());
            $temp['cat'] = $item->getCat()->getName();
            $temp['views'] = $item->getViews();
            $temp['url'] = $item->getUrl();
            $temp['submitter_gravatar_hash'] = $provider->gravatarHash($item->getSubmitter()->getEmail());
            $response[] = $temp;
        }
        return $this->json($response);
    }

    #[Route('/api/stack/content/get/{url}', name: 'app_stack_content_get')]
    public function app_stack_content_get($url,Jdate $jdate, Provider $provider,Request $request,SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $item = $entityManager->getRepository(StackContent::class)->findOneBy(['url'=>$url]);
        if(!$item)
            throw $this->createNotFoundException();
        $response = [];
        $response['id'] = $item->getId();
        $response['title'] = $item->getTitle();
        $response['body'] = $item->getBody();
        $response['submitter'] = $item->getSubmitter()->getFullName();
        $response['dateSubmit'] = $jdate->pastTime($item->getDateSubmit());
        $response['cat'] = $item->getCat()->getName();
        $response['views'] = $item->getViews();
        $response['url'] = $url;
        $response['submitter_gravatar_hash'] = $provider->gravatarHash($item->getSubmitter()->getEmail());
        return $this->json($response);
    }
    #[Route('/api/stack/content/getbyid/{id}', name: 'app_stack_content_get_by_id')]
    public function app_stack_content_get_by_id($id,Jdate $jdate, Provider $provider,Request $request,SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $item = $entityManager->getRepository(StackContent::class)->find($id);
        if(!$item)
            throw $this->createNotFoundException();
        $response = [];
        $response['id'] = $item->getId();
        $response['title'] = $item->getTitle();
        $response['body'] = $item->getBody();
        $response['submitter'] = $item->getSubmitter()->getFullName();
        $response['dateSubmit'] = $jdate->pastTime($item->getDateSubmit());
        $response['cat'] = $item->getCat()->getName();
        $response['views'] = $item->getViews();
        $response['url'] = $item->getUrl();
        $response['submitter_gravatar_hash'] = $provider->gravatarHash($item->getSubmitter()->getEmail());
        return $this->json($response);
    }
    #[Route('/api/stack/content/increase/view/{url}', name: 'app_stack_content_increase_view')]
    public function app_stack_content_increase_view($url,Jdate $jdate, Provider $provider,Request $request,SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $item = $entityManager->getRepository(StackContent::class)->findOneBy(['url'=>$url]);
        $item->setViews($item->getViews() + 1);
        $entityManager->persist($item);
        $entityManager->flush();
        return $this->json([
            'error' => 0,
            'result' => 'ok'
        ]);
    }

    #[Route('/api/stack/stat/get', name: 'app_stack_stat_get')]
    public function app_stack_stat_get(Jdate $jdate, Provider $provider,Request $request,SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {

        $response = [];
        $response['replayCount'] = $entityManager->getRepository(StackContent::class)->getAllReplayCount();
        $response['questionCount'] = $entityManager->getRepository(StackContent::class)->getStackCount();;
        return $this->json($response);
    }

    #[Route('/api/stack/content/delete/{url}', name: 'app_stack_content_delete')]
    public function app_stack_content_delete($url,Jdate $jdate, Provider $provider,Request $request,SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $content = $entityManager->getRepository(StackContent::class)->findOneBy(['url'=>$url]);
        if(!$content)
            throw $this->createNotFoundException();
        if($content->getSubmitter() === $this->getUser()){
            if(! $content->getUpper()){
                $items = $entityManager->getRepository(StackContent::class)->findBy([
                    'upper'=>$content,
                ]);
                foreach ($items as $item){
                    $entityManager->getRepository(StackContent::class)->remove($item,true);
                }
            }
            $entityManager->getRepository(StackContent::class)->remove($content,true);
            return $this->json([
                'error' => 0,
                'result' => 'ok'
            ]);
        }
        throw $this->createAccessDeniedException();
    }

    #[Route('/api/stack/content/deletebyid/{id}', name: 'app_stack_content_delete_by_id')]
    public function app_stack_content_delete_by_id($id,Jdate $jdate, Provider $provider,Request $request,SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $content = $entityManager->getRepository(StackContent::class)->find($id);
        if(!$content)
            throw $this->createNotFoundException();
        if($content->getSubmitter() === $this->getUser()){
            if(! $content->getUpper()){
                $items = $entityManager->getRepository(StackContent::class)->findBy([
                    'upper'=>$content,
                ]);
                foreach ($items as $item){
                    $entityManager->getRepository(StackContent::class)->remove($item,true);
                }
            }
            $entityManager->getRepository(StackContent::class)->remove($content,true);
            return $this->json([
                'error' => 0,
                'result' => 'ok'
            ]);
        }
        throw $this->createAccessDeniedException();
    }

    #[Route('/api/stack/edit/{id}', name: 'app_stack_content_edit')]
    public function app_stack_content_edit($id,Request $request,SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(array_key_exists('title',$params) && array_key_exists('cat',$params) && array_key_exists('body',$params  )){
            $stack = $entityManager->getRepository(StackContent::class)->find($id);
            if(! $stack)
                throw $this->createNotFoundException();
            $cat = $entityManager->getRepository(StackCat::class)->find($params['cat']);
            $stack->setBody($params['body']);
            $stack->setCat($cat);
            $stack->setTitle($params['title']);
            $entityManager->persist($stack);
            $entityManager->flush();
            return $this->json([
                'error'=> 0,
                'message'=> 'ok',
                'url'=>$stack->getUrl()
            ]);
        }
        return $this->json([
            'error'=> 999,
            'message'=> 'تمام موارد لازم را وارد کنید.'
        ]);
    }
}
