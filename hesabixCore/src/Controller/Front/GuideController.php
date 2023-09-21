<?php

namespace App\Controller\Front;

use App\Entity\GuideContent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GuideController extends AbstractController
{
    #[Route('/front/help/guide/{id}', name: 'general_help_guide')]
    public function general_help_guide(EntityManagerInterface $entityManager, String $id = 'general'): Response
    {
        $items = $entityManager->getRepository(GuideContent::class)->findBy(['cat'=>$id]);
        return $this->render('general/guide.html.twig',[
            'items'=>$items
        ]);
    }
}
