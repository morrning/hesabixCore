<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\HesabdariDoc;
use App\Entity\Person;
use App\Service\Access;
use App\Service\Log;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class PersonsController extends AbstractController
{
    #[Route('/api/person/info/{code}', name: 'app_persons_info')]
    public function app_persons_info($code,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('person');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $person = $entityManager->getRepository(Person::class)->findOneBy([
            'bid'=>$acc['bid'],
            'code'=>$code
        ]);
        return $this->json($person);
    }
    #[Route('/api/person/mod/{code}', name: 'app_persons_mod')]
    public function app_persons_mod(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,$code = 0): JsonResponse
    {
        $acc = $access->hasRole('person');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(!array_key_exists('nikename',$params))
            return $this->json(['result'=>-1]);
        if(count_chars(trim($params['nikename'])) == 0)
            return $this->json(['result'=>3]);
        if($code == 0){
            $person = $entityManager->getRepository(Person::class)->findOneBy([
                'nikename'=>$params['nikename'],
                'bid' =>$acc['bid']
            ]);
            //check exist before
            if($person)
                return $this->json(['result'=>2]);
            $person = new Person();
            $person->setCode($provider->getAccountingCode($request->headers->get('activeBid'),'person'));
        }
        else{
            $person = $entityManager->getRepository(Person::class)->findOneBy([
                'bid'=>$acc['bid'],
                'code'=>$code
            ]);
            if(!$person)
                throw $this->createNotFoundException();
        }
        $person->setBid($acc['bid']);
        $person->setNikename($params['nikename']);
        if(array_key_exists('name',$params))
            $person->setName($params['name']);
        if(array_key_exists('birthday',$params))
            $person->setBirthday($params['birthday']);
        if(array_key_exists('tel',$params))
            $person->setTel($params['tel']);
        if(array_key_exists('address',$params))
            $person->setAddress($params['address']);
        if(array_key_exists('des',$params))
            $person->setDes($params['des']);
        if(array_key_exists('mobile',$params))
            $person->setMobile($params['mobile']);
        if(array_key_exists('fax',$params))
            $person->setFax($params['fax']);
        if(array_key_exists('website',$params))
            $person->setWebsite($params['website']);
        if(array_key_exists('email',$params))
            $person->setEmail($params['email']);
        if(array_key_exists('postalcode',$params))
            $person->setPostalcode($params['postalcode']);
        if(array_key_exists('shahr',$params))
            $person->setShahr($params['shahr']);
        if(array_key_exists('ostan',$params))
            $person->setOstan($params['ostan']);
        if(array_key_exists('keshvar',$params))
            $person->setKeshvar($params['keshvar']);
        if(array_key_exists('sabt',$params))
            $person->setSabt($params['sabt']);
        if(array_key_exists('codeeghtesadi',$params))
            $person->setCodeeghtesadi($params['codeeghtesadi']);
        if(array_key_exists('shenasemeli',$params))
            $person->setShenasemeli($params['shenasemeli']);
        if(array_key_exists('company',$params))
            $person->setCompany($params['company']);
        $entityManager->persist($person);
        $entityManager->flush();
        $log->insert('اشخاص','شخص با نام مستعار ' . $params['nikename'] . ' افزوده/ویرایش شد.',$this->getUser(),$request->headers->get('activeBid'));
        return $this->json(['result' => 1]);
    }

    #[Route('/api/person/list', name: 'app_persons_list')]
    public function app_persons_list(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        if(!$access->hasRole('person'))
            throw $this->createAccessDeniedException();
        $persons = $entityManager->getRepository(Person::class)->findBy([
           'bid'=>$request->headers->get('activeBid')
        ]);
        return $this->json($persons);
    }

    #[Route('/api/person/list/print', name: 'app_persons_list_print')]
    public function app_persons_list_print(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('person');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(!array_key_exists('items',$params)){
            $persons = $entityManager->getRepository(Person::class)->findBy([
                'bid'=>$acc['bid']
            ]);
        }
        else{
            $persons = [];
            foreach ($params['items'] as $param){
                $prs = $entityManager->getRepository(Person::class)->findOneBy([
                    'id'=>$param['id'],
                    'bid'=>$acc['bid']
                ]);
                if($prs)
                    $persons[] = $prs;
            }
        }



        $pid = $provider->createPrint(
            $acc['bid'],
            $this->getUser(),
            $this->renderView('pdf/persons.html.twig',[
                'page_title'=>'فهرست اشخاص',
                'bid'=>$acc['bid'],
                'persons'=>$persons
            ]));
        return $this->json(['id'=>$pid]);
    }

    /**
     * @throws Exception
     */
    #[Route('/api/person/list/excel', name: 'app_persons_list_excel')]
    public function app_persons_list_excel(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): BinaryFileResponse | JsonResponse | StreamedResponse
    {
        $acc = $access->hasRole('person');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(!array_key_exists('items',$params)){
            $persons = $entityManager->getRepository(Person::class)->findBy([
                'bid'=>$acc['bid']
            ]);
        }
        else{
            $persons = [];
            foreach ($params['items'] as $param){
                $prs = $entityManager->getRepository(Person::class)->findOneBy([
                    'id'=>$param['id'],
                    'bid'=>$acc['bid']
                ]);
                if($prs)
                    $persons[] = $prs;
            }
        }
        return new BinaryFileResponse($provider->createExcell($persons));
    }

    #[Route('/api/person/receive/list/print', name: 'app_persons_receive_list_print')]
    public function app_persons_receive_list_print(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('person_receive');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(!array_key_exists('items',$params)){
            $items = $entityManager->getRepository(HesabdariDoc::class)->findBy([
                'bid'=>$acc['bid'],
                'type'=>'person_receive',
                'year'=>$acc['year']
            ]);
        }
        else{
            $items = [];
            foreach ($params['items'] as $param){
                $prs = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                    'id'=>$param['id'],
                    'bid'=>$acc['bid'],
                    'type'=>'person_receive',
                    'year'=>$acc['year']
                ]);
                if($prs)
                    $items[] = $prs;
            }
        }
        $pid = $provider->createPrint(
            $acc['bid'],
            $this->getUser(),
            $this->renderView('pdf/persons_receive.html.twig',[
                'page_title'=>'لیست دریافت‌ها',
                'bid'=>$acc['bid'],
                'items'=>$items
            ]));
        return $this->json(['id'=>$pid]);
    }

    /**
     * @throws Exception
     */
    #[Route('/api/person/receive/list/excel', name: 'app_persons_receive_list_excel')]
    public function app_persons_receive_list_excel(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): BinaryFileResponse | JsonResponse | StreamedResponse
    {
        $acc = $access->hasRole('person_receive');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(!array_key_exists('items',$params)){
            $items = $entityManager->getRepository(HesabdariDoc::class)->findBy([
                'bid'=>$acc['bid'],
                'type'=>'person_receive',
                'year'=>$acc['year']
            ]);
        }
        else{
            $items = [];
            foreach ($params['items'] as $param){
                $prs = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                    'id'=>$param['id'],
                    'bid'=>$acc['bid'],
                    'type'=>'person_receive',
                    'year'=>$acc['year']
                ]);
                if($prs)
                    $items[] = $prs;
            }
        }
        return new BinaryFileResponse($provider->createExcell($items,['type','dateSubmit']));
    }

    #[Route('/api/person/send/list/print', name: 'app_persons_send_list_print')]
    public function app_persons_send_list_print(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('person_send');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(!array_key_exists('items',$params)){
            $items = $entityManager->getRepository(HesabdariDoc::class)->findBy([
                'bid'=>$acc['bid'],
                'type'=>'person_send',
                'year'=>$acc['year']
            ]);
        }
        else{
            $items = [];
            foreach ($params['items'] as $param){
                $prs = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                    'id'=>$param['id'],
                    'bid'=>$acc['bid'],
                    'type'=>'person_send',
                    'year'=>$acc['year']
                ]);
                if($prs)
                    $items[] = $prs;
            }
        }
        $pid = $provider->createPrint(
            $acc['bid'],
            $this->getUser(),
            $this->renderView('pdf/persons_receive.html.twig',[
                'page_title'=>'لیست پرداخت‌ها',
                'bid'=>$acc['bid'],
                'items'=>$items
            ]));
        return $this->json(['id'=>$pid]);
    }

    /**
     * @throws Exception
     */
    #[Route('/api/person/send/list/excel', name: 'app_persons_send_list_excel')]
    public function app_persons_send_list_excel(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): BinaryFileResponse | JsonResponse | StreamedResponse
    {
        $acc = $access->hasRole('person_send');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(!array_key_exists('items',$params)){
            $items = $entityManager->getRepository(HesabdariDoc::class)->findBy([
                'bid'=>$acc['bid'],
                'type'=>'person_send',
                'year'=>$acc['year']
            ]);
        }
        else{
            $items = [];
            foreach ($params['items'] as $param){
                $prs = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                    'id'=>$param['id'],
                    'bid'=>$acc['bid'],
                    'type'=>'person_send',
                    'year'=>$acc['year']
                ]);
                if($prs)
                    $items[] = $prs;
            }
        }
        return new BinaryFileResponse($provider->createExcell($items,['type','dateSubmit']));
    }

}
