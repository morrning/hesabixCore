<?php

namespace App\Controller\Front;

use App\Entity\Business;
use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShortlinksController extends AbstractController
{
    #[Route('/sl/{type}/{bid}/{link}/{msg}', name: 'shortlinks_show')]
    public function shortlinks_show(string $bid, string $type, string $link, EntityManagerInterface $entityManager, String $msg = 'default'): Response
    {
        $bus = $entityManager->getRepository(Business::class)->find($bid);
        if (!$bus)
            throw $this->createNotFoundException();
        if (!$bus->isShortlinks())
            throw $this->createNotFoundException();
        if ($type == 'sell') {
            $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                'type' => 'sell',
                'shortlink' => $link
            ]);
            if (!$doc) {
                $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                    'type' => 'sell',
                    'id' => $link,
                    'bid'=>$bus
                ]);
                if (!$doc)
                    throw $this->createNotFoundException();
            }
            
            $rows = $entityManager->getRepository(HesabdariRow::class)->findBy(['doc' => $doc]);
            $items = [];
            $person = null;
            foreach ($rows as $row) {
                if (!$row->getPerson())
                    $items[] = $row;
                else
                    $person = $row->getPerson();
            }
            //calculate total pays
            $totalPays = 0;
            foreach ($doc->getRelatedDocs() as $relatedDoc)
                $totalPays += $relatedDoc->getAmount();
        }
        return $this->render('shortlinks/sell.html.twig', [
            'bid' => $doc->getBid(),
            'doc' => $doc,
            'rows' => $items,
            'person'=> $person,
            'totalPays'=>$totalPays,
            'msg'=>$msg
        ]);
    }
}
