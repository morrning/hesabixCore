<?php

namespace App\Controller\Plugins\Hrm;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\PlugGhestaDoc;
use App\Entity\PlugGhestaItem;
use App\Entity\HesabdariDoc;
use App\Entity\Person;
use App\Service\Access;
use App\Service\Provider;
use App\Service\Printers;
use App\Entity\PrintOptions;
use App\Service\Log;
use App\Entity\Business;

class DocsController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    
}