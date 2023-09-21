<?php

namespace App\Controller\Front;

use App\Entity\BlogComment;
use App\Entity\BlogPost;
use App\Form\BlogPostType;
use App\Form\CommentType;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class BlogController extends AbstractController
{
    #[Route('/front/blog/home/{page}', name: 'general_blog_home')]
    public function general_help_guide(Provider $provider,EntityManagerInterface $entityManager, String $page = '1'): Response
    {
        $items = $entityManager->getRepository(BlogPost::class)->search(['page'=>$page,'count'=>10]);
        $nextPage = true;
        if((int)($entityManager->getRepository(BlogPost::class)->getAllContentCount()/10)  <= $page)
            $nextPage = false;
        return $this->render('blog/list.html.twig',[
            'items'=>$items,
            'nextPage'=>$nextPage,
            'page'=>$page
        ]);
    }

    #[Route('/front/blog/post/{url}', name: 'general_blog_post')]
    public function general_blog_post(Request $request,EntityManagerInterface $entityManager, String $url): Response
    {
        $item = $entityManager->getRepository(BlogPost::class)->findOneBy(['url'=>$url]);
        if(!$item) $this->createNotFoundException();

        $comment = new BlogComment();
        $form = $this->createForm(CommentType::class,$comment);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid() && $this->getUser()){
            $oldComments = $entityManager->getRepository(BlogComment::class)->findBy([
                'submitter'=>$this->getUser()
            ],['id'=>'DESC']);
            if(count($oldComments) == 0){
                $comment->setDateSubmit(time());
                $comment->setPost($item);
                $comment->setSubmitter($this->getUser());
                $entityManager->persist($comment);
                $entityManager->flush();
                $comment->setBody('');
                $form->addError(new FormError('دیدگاه شما ثبت شد بعد از تایید مدیر منتشر خواهد شد.'));
            }
            else{
                if($oldComments[0]->getDateSubmit() > time() + 300){
                    $comment->setDateSubmit(time());
                    $comment->setPost($item);
                    $comment->setSubmitter($this->getUser());
                    $entityManager->persist($comment);
                    $entityManager->flush();
                    $comment->setBody('');
                    $form->addError(new FormError('دیدگاه شما ثبت شد بعد از تایید مدیر منتشر خواهد شد.'));
                }
                else{
                    $form->addError(new FormError('شما اخیرا یک دیدگاه ارسال کرده اید. ۵ دقیقه دیگر مجددا سعی کنید.'));
                }
            }
        }
        return $this->render('blog/post.html.twig',[
            'item'=>$item,
            'comments'=>$entityManager->getRepository(BlogComment::class)->findBy([
                'post'=>$item,
                'publish'=>true,
            ],['id'=>'DESC']),
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/app/blog/posts/list", name="app_front_blog_list")
     */
    public function app_front_blog_list(EntityManagerInterface $entityManager): Response
    {
        return $this->render('/app/blog/posts.html.twig',[
            'items'=>$entityManager->getRepository(BlogPost::class)->findBy([],[
                'id'=>'DESC'
            ])
        ]);
    }

    /**
     * @Route("/app/blog/post/new", name="app_front_blog_new")
     */
    public function app_front_blog_new(SluggerInterface $slugger,Request $request,EntityManagerInterface $entityManager): Response
    {
        $item = new BlogPost();
        $form = $this->createForm(BlogPostType::class,$item,[]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $brochureFile = $form->get('img')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('blogMediaDir'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $item->setImg($newFilename);
            }
            $item->setDateSubmit(time());
            $url = str_replace(' ','_',$item->getTitle());
            $check = $entityManager->getRepository(BlogPost::class)->findOneBy(['url'=>$url]);
            $item->setUrl($url);
            if($check){
                $item->setUrl($url . $url);
            }
            $item->setViews(0);
            $item->setSubmitter($this->getUser());
            $entityManager->persist($item);
            $entityManager->flush();
            return $this->redirectToRoute('app_front_blog_list');
        }
        return $this->render('/app/blog/new.html.twig',[
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/app/blog/post/delete/{id}", name="app_front_blog_delete")
     */
    public function app_front_blog_delete(String $id,EntityManagerInterface $entityManager): Response
    {
        $item = $entityManager->getRepository(BlogPost::class)->find($id);
        if($item){
            $entityManager->remove($item);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_front_blog_list');
    }

    /**
     * @Route("/app/blog/post/edit/{id}", name="app_front_blog_edit")
     */
    public function app_front_blog_edit(String $id,SluggerInterface $slugger,Request $request,EntityManagerInterface $entityManager): Response
    {
        $item = $entityManager->getRepository(BlogPost::class)->find($id);
        if(!$item) $this->createNotFoundException();
        $oldFileName = $item->getImg();
        $form = $this->createForm(BlogPostType::class,$item,[]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $brochureFile = $form->get('img')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('blogMediaDir'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $item->setImg($newFilename);
            }
            else{
                $item->setImg($oldFileName);
            }
            $entityManager->persist($item);
            $entityManager->flush();
            return $this->redirectToRoute('app_front_blog_list');
        }
        return $this->render('/app/blog/new.html.twig',[
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/app/blog/comments/list", name="app_front_comments_list")
     */
    public function app_front_comments_list(EntityManagerInterface $entityManager): Response
    {
        return $this->render('/app/blog/comments.html.twig',[
            'items'=>$entityManager->getRepository(BlogComment::class)->findBy([],[
                'id'=>'DESC'
            ])
        ]);
    }

    /**
     * @Route("/app/blog/comment/delete/{id}", name="app_front_comment_delete")
     */
    public function app_front_comment_delete(String $id,EntityManagerInterface $entityManager): Response
    {
        $item = $entityManager->getRepository(BlogComment::class)->find($id);
        if($item){
            $entityManager->remove($item);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_front_comments_list');
    }

    /**
     * @Route("/app/blog/comment/toggle/{id}", name="app_front_comment_toggle")
     */
    public function app_front_comment_toggle(String $id,EntityManagerInterface $entityManager): Response
    {
        $item = $entityManager->getRepository(BlogComment::class)->find($id);
        if($item){
            if($item->isPublish()){
                $item->setPublish(false);
            }
            else{
                $item->setPublish(true);
            }
            $entityManager->persist($item);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_front_comments_list');
    }
}
