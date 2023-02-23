<?php

namespace App\Controller;

use App\Entity\BlogCat;
use App\Entity\BlogPost;
use App\Entity\StackCat;
use App\Entity\StackContent;
use App\Service\Jdate;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class BlogController extends AbstractController
{
    #[Route('/api/blog/cats/get', name: 'app_blog_get_cats')]
    public function app_blog_get_cats(SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $result = [];
        $cats = $entityManager->getRepository(BlogCat::class)->findAll();
        foreach ($cats as $cat){
            $temp = [];
            $temp['id'] = $cat->getId();
            $temp['name'] = $cat->getLabel();
            $temp['code'] = $cat->getCode();
            $result[] = $temp;
        }
        return $this->json($result);
    }

    #[Route('/api/blog/insert', name: 'app_blog_content_insert')]
    public function app_blog_content_insert(Request $request,SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(array_key_exists('intro',$params) && array_key_exists('title',$params) && array_key_exists('cat',$params) && array_key_exists('body',$params  )){
            $cat = $entityManager->getRepository(BlogCat::class)->find($params['cat']);
            $post = new BlogPost();
            $post->setBody($params['body']);
            $post->setCat($cat);
            $post->setIntero($params['intro']);
            $post->setImg($params['img']);
            $post->setTitle($params['title']);
            $post->setDateSubmit(time());
            $post->setViews(1);
            $post->setSubmitter($this->getUser());
            $post->setUrl(str_replace(' ','_',$params['title']));
            $entityManager->persist($post);
            $entityManager->flush();
            return $this->json([
                'error'=> 0,
                'message'=> 'ok',
                'url'=>$post->getUrl()
            ]);
        }
        return $this->json([
            'error'=> 999,
            'message'=> 'تمام موارد لازم را وارد کنید.'
        ]);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    #[Route('/api/blog/contents/search', name: 'app_blog_contents_get')]
    public function app_blog_contents_get(Jdate $jdate, Provider $provider,Request $request,SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        $params = $provider->createSearchParams($request);
        $items = $entityManager->getRepository(BlogPost::class)->search($params);
        $response = [];
        foreach ($items as $item){
            $temp = [];
            $temp['id'] = $item->getId();
            $temp['title'] = $item->getTitle();
            $temp['intero'] = $item->getIntero();
            $temp['body'] = $item->getBody();
            $temp['submitter'] = $item->getSubmitter()->getFullName();
            $temp['dateSubmit'] = $jdate->pastTime($item->getDateSubmit());
            $temp['cat'] = $item->getCat()->getLabel();
            $temp['views'] = $item->getViews();
            $temp['url'] = $item->getUrl();
            $temp['img'] = $item->getImg();
            $response[] = $temp;
        }
        // calc has next page
        $nextPage = true;
        if((int)$provider->maxPages($params,$entityManager->getRepository(BlogPost::class)->getAllContentCount()) == $params['page'])
            $nextPage = false;
        return $this->json([
            'data'=>$response,
            'nextPage'=>$nextPage
        ]);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    #[Route('/api/blog/post/get/{url}', name: 'app_blog_post_get')]
    public function app_blog_post_get($url,Jdate $jdate, Provider $provider,Request $request,SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $post = $entityManager->getRepository(BlogPost::class)->findOneBy(['url'=>$url]);
        if(!$post)
            throw $this->createNotFoundException();
        $temp = [];
        $temp['id'] = $post->getId();
        $temp['title'] = $post->getTitle();
        $temp['intero'] = $post->getIntero();
        $temp['body'] = $post->getBody();
        $temp['submitter'] = $post->getSubmitter()->getFullName();
        $temp['dateSubmit'] = $jdate->pastTime($post->getDateSubmit());
        $temp['cat'] = $post->getCat()->getLabel();
        $temp['views'] = $post->getViews();
        $temp['url'] = $post->getUrl();
        $temp['img'] = $post->getImg();
        return $this->json($temp);
    }
}
