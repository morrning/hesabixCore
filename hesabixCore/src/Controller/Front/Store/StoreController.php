<?php

namespace App\Controller\Front\Store;

use App\Entity\Business;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StoreController extends AbstractController
{
    protected $em;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    private function CheckBID($id):bool|Business{
        $bid = $this->em->getRepository(Business::class)->findOneBy(['storeUsername'=>$id]);
        if(!$bid)
            return false;
        return $bid;
    }
    #[Route('/s/{id}', name: 'app_store')]
    public function app_store(String $id,EntityManagerInterface $entityManager): Response
    {
        $bid = $this->CheckBID($id);
        if(!$bid)
            throw $this->createNotFoundException();

        return $this->render('store/index.html.twig', [
            'bid' => $bid,
        ]);
    }
}
