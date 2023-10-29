<?php

namespace App\Controller\Front;

use App\Entity\APIDocument;
use App\Entity\ChangeReport;
use App\Entity\GuideContent;
use App\Entity\Support;
use App\Form\APIDocumentType;
use App\Form\GuideType;
use App\Form\SupportType;
use App\Form\UpdateListType;
use App\Service\SMS;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @Route("/app/dashboard", name="app_front_dashboard")
     */
    public function app_front_app(): Response
    {
        return $this->render('/app/dashboard.html.twig');
    }
    /**
     * @Route("/app/changes/list", name="app_front_changes_list")
     */
    public function app_front_changes_list(EntityManagerInterface $entityManager): Response
    {
        return $this->render('/app/changes/list.html.twig',[
            'items'=>$entityManager->getRepository(ChangeReport::class)->findAll()
        ]);
    }
    /**
     * @Route("/app/changes/delete/{id}", name="app_front_changes_delete")
     */
    public function app_front_changes_delete(String $id,EntityManagerInterface $entityManager): Response
    {
        $item = $entityManager->getRepository(ChangeReport::class)->find($id);
        if($item){
            $entityManager->remove($item);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_front_changes_list');
    }
    /**
     * @Route("/app/changes/new", name="app_front_changes_new")
     */
    public function app_front_changes_new(Request $request,EntityManagerInterface $entityManager): Response
    {
        $change = new ChangeReport();
        $change->setDateSubmit(time());
        $form = $this->createForm(UpdateListType::class,$change,[]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($change);
            $entityManager->flush();
            return $this->redirectToRoute('app_front_changes_list');
        }
        return $this->render('/app/changes/new.html.twig',[
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/app/changes/edit/{id}", name="app_front_changes_edit")
     */
    public function app_front_changes_edit(string $id, Request $request,EntityManagerInterface $entityManager): Response
    {
        $change = $entityManager->getRepository(ChangeReport::class)->find($id);
        if(!$change)
            throw $this->createNotFoundException();
        $change->setDateSubmit(time());
        $form = $this->createForm(UpdateListType::class,$change,[]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($change);
            $entityManager->flush();
            return $this->redirectToRoute('app_front_changes_list');
        }
        return $this->render('/app/changes/new.html.twig',[
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/app/api/list", name="app_front_api_list")
     */
    public function app_front_api_list(EntityManagerInterface $entityManager): Response
    {
        return $this->render('/app/api/list.html.twig',[
            'items'=>$entityManager->getRepository(APIDocument::class)->findAll()
        ]);
    }
    /**
     * @Route("/app/api/new", name="app_front_api_new")
     */
    public function app_front_api_new(Request $request,EntityManagerInterface $entityManager): Response
    {
        $item = new APIDocument();
        $form = $this->createForm(APIDocumentType::class,$item,[]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($item);
            $entityManager->flush();
            return $this->redirectToRoute('app_front_api_list');
        }
        return $this->render('/app/api/new.html.twig',[
            'form'=>$form->createView()
        ]);
    }
    /**
     * @Route("/app/api/edit/{id}", name="app_front_api_edit")
     */
    public function app_front_api_edit(Request $request,EntityManagerInterface $entityManager,string $id): Response
    {
        $item = $entityManager->getRepository(APIDocument::class)->find($id);
        if(!$item)
            throw $this->createNotFoundException();

        $form = $this->createForm(APIDocumentType::class,$item,[]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($item);
            $entityManager->flush();
            return $this->redirectToRoute('app_front_api_list');
        }
        return $this->render('/app/api/new.html.twig',[
            'form'=>$form->createView()
        ]);
    }
    /**
     * @Route("/app/api/delete/{id}", name="app_front_api_delete")
     */
    public function app_front_api_delete(String $id,EntityManagerInterface $entityManager): Response
    {
        $item = $entityManager->getRepository(APIDocument::class)->find($id);
        if($item){
            if( $item->getId() != 1){
                $entityManager->remove($item);
                $entityManager->flush();
            }

        }
        return $this->redirectToRoute('app_front_api_list');
    }

    /**
     * @Route("/app/guide/list", name="app_front_guide_list")
     */
    public function app_front_guide_list(EntityManagerInterface $entityManager): Response
    {
        return $this->render('/app/guide/list.html.twig',[
            'items'=>$entityManager->getRepository(GuideContent::class)->findAll()
        ]);
    }

    /**
     * @Route("/app/guide/delete/{id}", name="app_front_guide_delete")
     */
    public function app_front_guide_delete(String $id,EntityManagerInterface $entityManager): Response
    {
        $item = $entityManager->getRepository(GuideContent::class)->find($id);
        if($item){
            $entityManager->remove($item);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_front_guide_list');
    }

    /**
     * @Route("/app/guide/new", name="app_front_guide_new")
     */
    public function app_front_guide_new(Request $request,EntityManagerInterface $entityManager): Response
    {
        $item = new GuideContent();
        $form = $this->createForm(GuideType::class,$item,[]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $item->setDateSubmit(time());
            $item->setUrl(0);
            $item->setSubmiter($this->getUser());
            $entityManager->persist($item);
            $entityManager->flush();
            return $this->redirectToRoute('app_front_guide_list');
        }
        return $this->render('/app/guide/new.html.twig',[
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/app/guide/edit/{id}", name="app_front_guide_edit")
     */
    public function app_front_guide_edit(string $id, Request $request,EntityManagerInterface $entityManager): Response
    {
        $item = $entityManager->getRepository(GuideContent::class)->find($id);
        if(!$item)
            throw $this->createNotFoundException();
        $form = $this->createForm(GuideType::class,$item,[]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $item->setDateSubmit(time());
            $item->setUrl(0);
            $item->setSubmiter($this->getUser());
            $entityManager->persist($item);
            $entityManager->flush();
            return $this->redirectToRoute('app_front_guide_list');
        }
        return $this->render('/app/guide/new.html.twig',[
            'form'=>$form->createView()
        ]);
    }

    /**
     * @throws \Exception
     */
    #[Route('/app/database/sync', name: 'app_front_sync_database')]
    public function app_front_sync_database(KernelInterface $kernel): Response
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'doctrine:schema:update',
            // (optional) define the value of command arguments
            '--force' => true,
            '--complete' => true
        ]);

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);

        // return the output, don't use if you used NullOutput()
        $content = $output->fetch();
        return $this->render('/app/sync-database.html.twig',[
            'content'=>$content
        ]);
    }
}
