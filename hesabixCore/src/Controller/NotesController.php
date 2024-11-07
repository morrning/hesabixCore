<?php
namespace App\Controller;

use App\Entity\HesabdariDoc;
use App\Service\Extractor;
use App\Service\Log;
use App\Service\Jdate;
use App\Entity\Note;
use App\Service\Access;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class NotesController extends AbstractController
{
    #[Route('/api/notes/list', name: 'api_notes_list')]
    public function api_notes_list(Extractor $extractor, Request $request, Access $access, Jdate $jdate, EntityManagerInterface $entityManager, Log $log): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if ($params['code'] != 0) {
            $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                'bid' => $acc['bid'],
                'code' => $params['code'],
                'money' => $acc['money']
            ]);
            if (!$doc)
                return $this->json($extractor->notFound());
        }
        $items = $entityManager->getRepository(Note::class)->findBy([
            'bid' => $acc['bid'],
            'type' => $params['type'],
            'doc' => $doc
        ]);
        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'id' => $item->getId(),
                'des' => $item->getDes(),
                'submitter' => $item->getSubmitter()->getFullName(),
                'date' => $jdate->jdate('Y/n/d', $item->getDate())
            ];
        }
        return $this->json($result);
    }

    #[Route('/api/notes/count', name: 'api_notes_count')]
    public function api_notes_count(Extractor $extractor, Request $request, Access $access, Jdate $jdate, EntityManagerInterface $entityManager, Log $log): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if ($params['code'] != 0) {
            $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                'bid' => $acc['bid'],
                'code' => $params['code'],
                'money' => $acc['money']
            ]);
            if (!$doc)
                return $this->json($extractor->notFound());
        }
        $items = $entityManager->getRepository(Note::class)->findBy([
            'bid' => $acc['bid'],
            'type' => $params['type'],
            'doc' => $doc
        ]);
        return $this->json(count($items));
    }

    #[Route('/api/notes/add', name: 'api_notes_add')]
    public function api_notes_add(Request $request, Access $access, Extractor $extractor, EntityManagerInterface $entityManager, Log $log): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
            'bid' => $acc['bid'],
            'code' => $params['code'],
            'money' => $acc['money']
        ]);
        if (!$doc)
            return $this->json($extractor->notFound());
        $note = new Note();
        $note->setDoc($doc);
        $note->setBid($acc['bid']);
        $note->setSubmitter($this->getUser());
        $note->setType($params['type']);
        $note->setDes($params['des']);
        $note->setDate(time());
        $entityManager->persist($note);
        $entityManager->flush();
        $log->insert(
            'حسابداری',
            ' افزودن یاداشت به فاکتور‌ شماره ' . $doc->getCode(),
            $this->getUser(),
            $acc['bid']->getId(),
            $doc
        );
        return $this->json(['result' => 1]);
    }
    #[Route('/api/notes/remove/{id}', name: 'api_notes_remove')]
    public function api_notes_remove(string $id, Access $access, EntityManagerInterface $entityManager, Log $log): JsonResponse
    {
        $acc = $access->hasRole('join');
        if (!$acc)
            throw $this->createAccessDeniedException();
        $item = $entityManager->getRepository(Note::class)->findOneBy([
            'bid' => $acc['bid'],
            'submitter' => $this->getUser(),
            'id' => $id
        ]);
        $entityManager->remove($item);
        $entityManager->flush();

        $log->insert(
            'حسابداری',
            ' حذف یاداشت از فاکتور‌ شماره ' . $item->getDoc()->getCode(),
            $this->getUser(),
            $acc['bid']->getId(),
            $item->getDoc()
        );
        return $this->json(['result' => 1]);
    }
}
