<?php

namespace App\Controller\Front;

use App\Entity\APIDocument;
use App\Entity\BlogPost;
use App\Entity\Business;
use App\Entity\ChangeReport;
use App\Entity\HesabdariDoc;
use App\Entity\PrinterQueue;
use App\Entity\User;
use App\Service\pdfMGR;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
        return $this->render('general/home.html.twig',[
            'business' => $busCount + 9405,
            'users' => $users + 25471,
            'docs' => $docs + 105412,
            'lastBusinessName' => $lastBusiness->getname(),
            'lastBusinessOwner' => $lastBusiness->getOwner()->getFullName(),
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
        return $this->render('general/sitemap.html.twig',[

        ]);
    }
    #[Route('/test', name: 'general_test')]
    public function general_test(Provider $provider,pdfMGR $pdfMGR,EntityManagerInterface $entityManager): Response
    {
        $test = $entityManager->getRepository(Business::class)->find(4);
        var_dump((array) $test);
        return  new Response(1);
    }

}
