<?php

namespace App\Controller\Front;

use App\Entity\APIDocument;
use App\Entity\BlogPost;
use App\Entity\Business;
use App\Entity\ChangeReport;
use App\Entity\HesabdariDoc;
use App\Entity\PrinterQueue;
use App\Entity\User;
use App\Entity\Settings;
use App\Service\pdfMGR;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Email;
use App\Service\SMS;
use Dompdf\Dompdf;

class UiGeneralController extends AbstractController
{
    #[Route('/', name: 'general_home')]
    public function general_home(SMS $sms,EntityManagerInterface $entityManager): Response
    {
        $busCount = count($entityManager->getRepository(Business::class)->findAll());
        $users = count($entityManager->getRepository(User::class)->findAll());
        $docs = count($entityManager->getRepository(HesabdariDoc::class)->findAll());
        $lastBusiness = $entityManager->getRepository(Business::class)->findLast();
        if($lastBusiness)
            return $this->render('general/home.html.twig',[
                'business' => $busCount + 11405,
                'users' => $users + 29471,
                'docs' => $docs + 105412,
                'lastBusinessName' => $lastBusiness->getname(),
                'lastBusinessOwner' => $lastBusiness->getOwner()->getFullName(),
                'blogPosts'=> $entityManager->getRepository(BlogPost::class)->findBy([],['dateSubmit'=>'DESC'],3)
            ]);
        return $this->render('general/home.html.twig',[
                'business' => $busCount + 11405,
                'users' => $users + 29471,
                'docs' => $docs + 105412,
                'lastBusinessName' => 'ثبت نشده',
                'lastBusinessOwner' => 'ثبت نشده',
                'blogPosts'=> $entityManager->getRepository(BlogPost::class)->findBy([],['dateSubmit'=>'DESC'],3)
        ]);
    }

    #[Route('/front/faq', name: 'general_faq')]
    public function general_faq(EntityManagerInterface $entityManager): Response
    {
        return $this->render('general/faq.html.twig',);
    }
    #[Route('/front/about', name: 'general_about')]
    public function general_about(EntityManagerInterface $entityManager): Response
    {
        return $this->render('general/about.html.twig',);
    }
    #[Route('/front/contact', name: 'general_contact')]
    public function general_contact(EntityManagerInterface $entityManager): Response
    {
        return $this->render('general/contact.html.twig',);
    }
    #[Route('/front/terms', name: 'general_terms')]
    public function general_terms(EntityManagerInterface $entityManager): Response
    {
        return $this->render('general/terms.html.twig',);
    }
    #[Route('/front/privacy', name: 'general_privacy')]
    public function general_privacy(EntityManagerInterface $entityManager): Response
    {
        return $this->render('general/privacy.html.twig',);
    }
    #[Route('/front/open-source', name: 'general_opensource')]
    public function general_opensource(EntityManagerInterface $entityManager): Response
    {
        return $this->render('general/opensource.html.twig',);
    }
    #[Route('/front/update-list', name: 'general_changes_reports')]
    public function general_changes_reports(EntityManagerInterface $entityManager): Response
    {
        return $this->render('general/update-list.html.twig',[
            'items'=>$entityManager->getRepository(ChangeReport::class)->findBy([],['id'=>'DESC'])
        ]);
    }

    #[Route('/front/help/api/{id}', name: 'general_help_api')]
    public function general_help_api(EntityManagerInterface $entityManager, String $id = '1'): Response
    {
        $item = $entityManager->getRepository(APIDocument::class)->find($id);
        if(!$item)
            throw $this->createNotFoundException();

        return $this->render('general/api.html.twig',[
            'cats'=>$entityManager->getRepository(APIDocument::class)->findBy([],['title'=>'ASC']),
            'item'=>$item
        ]);
    }

    #[Route('/front/sponsors', name: 'general_sponsors')]
    public function general_sponsors(EntityManagerInterface $entityManager): Response
    {
        return $this->render('general/sponsors.html.twig',[

        ]);
    }

    #[Route('/sitemap.xml', name: 'general_sitemap')]
    public function general_sitemap(EntityManagerInterface $entityManager): Response
    {
        $response = new Response('',200,['Content-Type'=>'text/xml']);
        return $this->render('general/sitemap.html.twig',[
            'timeNow'=>$dateTime = date('c', time()),
            'blogs'=>$entityManager->getRepository(BlogPost::class)->findAll(),
            'docs'=>$entityManager->getRepository(APIDocument::class)->findAll()
        ],$response);
    }
    #[Route('/front/features/{id}', name: 'general_features')]
    public function general_features(string $id,EntityManagerInterface $entityManager): Response
    {
        if($id == 'setup')
            return $this->render('/general/features/setup.html.twig');
        elseif($id == 'user_management')
            return $this->render('/general/features/user_managment.html.twig');
        elseif($id == 'buy_sell')
            return $this->render('/general/features/buy_sell.html.twig');
        throw $this->createNotFoundException();
    }

    #[Route('/front/apps/woocommerce', name: 'general_apps_woocommerce')]
    public function general_apps_woocommerce(EntityManagerInterface $entityManager): Response
    {
        return $this->render('general/woocommerce.html.twig',);
    }

    #[Route('/front/apps/repservice', name: 'general_apps_repservice')]
    public function general_apps_repservice(EntityManagerInterface $entityManager): Response
    {
        return $this->render('general/repservice.html.twig',);
    }

    #[Route('/front/apps/hesabixbox', name: 'general_apps_hesabixbox')]
    public function general_apps_hesabixbox(EntityManagerInterface $entityManager): Response
    {
        return $this->render('general/hesabixbox.html.twig',);
    }

    #[Route('/api/system/get/data', name: 'general_apps_get_data')]
    public function general_apps_get_data(EntityManagerInterface $entityManager): JsonResponse
    {
        $settings = $entityManager->getRepository(Settings::class)->findAll()[0];
        return $this->json([
            'footer' => $settings->getFooter()
        ]);
    }

}
