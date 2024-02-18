<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Entity\Cheque;
use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Entity\HesabdariTable;
use App\Service\Log;
use App\Service\Jdate;
use App\Service\Access;
use App\Service\Explore;
use App\Service\JsonResp;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChequeController extends AbstractController
{
    #[Route('/api/cheque/list', name: 'app_cheque_list')]
    public function app_accounting_insert(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,Jdate $jdate): JsonResponse
    {
        $acc = $access->hasRole('cheque');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $chequesInput = $entityManager->getRepository(Cheque::class)->findBy([
            'bid'=>$acc['bid'],
            'type'=>'input'
        ]);
        return $this->json([
            'input'=>Explore::SerializeCheques(array_reverse($chequesInput))
        ]);
    }

    #[Route('/api/cheque/pass/{id}', name: 'app_cheque_pass')]
    public function app_cheque_pass(string $id,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,Jdate $jdate): JsonResponse
    {
        $acc = $access->hasRole('cheque');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(! array_key_exists('bank',$params) || ! array_key_exists('date',$params))
            $this->createNotFoundException();
        $cheque = $entityManager->getRepository(Cheque::class)->findOneBy([
            'bid'=>$acc['bid'],
            'type'=>'input',
            'id' => $id
        ]);
        $bank = $entityManager->getRepository(BankAccount::class)->findOneBy([
            'bid'=>$acc['bid'],
            'code' => $params['bank']['code']
        ]);
        if(!$cheque || !$bank)
            throw $this->createNotFoundException();
        if($cheque->isLocked())
            throw $this->createAccessDeniedException('cheque operation not permitted');
        
        //edit cheque info
        $cheque->setBank($bank);
        $cheque->setStatus('پاس شده');
        $cheque->setDate($params['date']);
        $cheque->setLocked(true);
        $entityManager->persist($cheque);

        //create cheque document
        $hesabdariDoc = new HesabdariDoc;
        $hesabdariDoc->setBid($acc['bid']);
        $hesabdariDoc->setSubmitter($this->getUser());
        $hesabdariDoc->setYear($acc['year']);
        $hesabdariDoc->setMoney($acc['bid']->getMoney());
        $hesabdariDoc->setDateSubmit(time());
        $hesabdariDoc->setDate($params['date']);
        $hesabdariDoc->setType('pass_cheque');
        $hesabdariDoc->setCode($provider->getAccountingCode($acc['bid'],'accounting'));
        $hesabdariDoc->setDes($params['des']);
        $hesabdariDoc->setAmount($cheque->getAmount());
        $entityManager->persist($hesabdariDoc);

        //cheate hesabdari rows
        $hesabdariRow1 = new HesabdariRow();
        $hesabdariRow1->setDoc($hesabdariDoc);
        $hesabdariRow1->setCheque($cheque);
        $hesabdariRow1->setPerson($cheque->getPerson());
        $hesabdariRow1->setYear($acc['year']);
        $hesabdariRow1->setBs($cheque->getAmount());
        $hesabdariRow1->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code'=>3]));
        $hesabdariRow1->setBd(0);
        $hesabdariRow1->setBid($acc['bid']);
        $hesabdariRow1->setDes('پاس شدن چک و انتقال به بانک');
        $entityManager->persist($hesabdariRow1);

        $hesabdariRow2 = new HesabdariRow();
        $hesabdariRow2->setDoc($hesabdariDoc);
        $hesabdariRow2->setCheque($cheque);
        $hesabdariRow2->setBank($bank);
        $hesabdariRow2->setYear($acc['year']);
        $hesabdariRow2->setBs(0);
        $hesabdariRow2->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code'=>5]));
        $hesabdariRow2->setBd($cheque->getAmount());
        $hesabdariRow2->setBid($acc['bid']);
        $hesabdariRow2->setDes('پاس شدن چک و انتقال به بانک');
        $entityManager->persist($hesabdariRow2);
        $entityManager->flush();
        $log->insert(
            'حسابداری','ثبت چک پاس شده شماره ' . $cheque->getNumber() . ' و ثبت واریز به بانک ' . $bank->getName(),
            $this->getUser(),
            $acc['bid']->getId(),
            $hesabdariDoc
        );

        return $this->json([
            'result'=>'ok'
        ]);
    }
}
