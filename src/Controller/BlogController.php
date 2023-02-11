<?php

namespace App\Controller;

use App\Entity\BlogCat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class BlogController extends AbstractController
{
    #[Route('/api/blog/get_cats', name: 'app_blog_get_cats')]
    public function app_blog_get_cats(SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        return $this->json([
            'error' => 0,
            'data' => $serializer->serialize($entityManager->getRepository(BlogCat::class)->findAll(),'json'),
        ]);
    }
}
