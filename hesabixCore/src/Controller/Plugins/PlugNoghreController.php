<?php

namespace App\Controller\Plugins;

use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Entity\HesabdariTable;
use App\Entity\Person;
use App\Entity\PlugNoghreOrder;
use App\Service\Access;
use App\Service\Log;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlugNoghreController extends AbstractController
{

    #[Route('/api/plugin/noghre/employess/list', name: 'app_plug_noghre_employees')]
    public function app_plug_noghre_employees(EntityManagerInterface $entityManager,Access $access,Request $request): Response
    {
        if(!$access->hasRole('plugNoghreAdmin'))
            throw $this->createAccessDeniedException();
        $data = $entityManager->getRepository(Person::class)->findPlugNoghreEmplyess($request->headers->get('activeBid'));
        return $this->json($data);
    }
    #[Route('/api/plugin/noghre/employess/mod/{id}', name: 'app_plug_noghre_employe_mod')]
    public function app_plug_noghre_employe_mod(Log $log,Request $request,EntityManagerInterface $entityManager,Access $access,String $id): Response
    {
        $acc = $access->hasRole('plugNoghreAdmin');
        if(!$acc) throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(!array_key_exists('hakak',$params) ||!array_key_exists('ghalam',$params) || !array_key_exists('tarash',$params) || !array_key_exists('morsa',$params))
            throw $this->createAccessDeniedException('params incomplete');
        $person = $entityManager->getRepository(Person::class)->findOneBy(['code'=>$id]);
        if(!$person)
            throw $this->createNotFoundException('person not found');
        $person->setPlugNoghreMorsa($params['morsa']);
        $person->setPlugNoghreHakak($params['hakak']);
        $person->setPlugNoghreTarash($params['tarash']);
        $person->setPlugNoghreGhalam($params['ghalam']);
        $person->setEmploye(false);
        if($params['morsa'] || $params['tarash'] || $params['hakak'])
            $person->setEmploye(true);
        $entityManager->persist($person);
        $entityManager->flush();
        $data = $entityManager->getRepository(Person::class)->findPlugNoghreEmplyess($request->headers->get('activeBid'));
        $log->insert(
            'کارگاه منسوجات نقره',
        'مشخصات شاغل با نام ' . $person->getNikename() . '  ویرایش / افزوده شد.',
        $this->getUser(),
        $acc['bid']);
        return $this->json($data);
    }
    #[Route('/api/plugin/noghre/ghalam/list', name: 'app_plug_noghre_employees_ghalam_list')]
    public function app_plug_noghre_employees_ghalam_list(EntityManagerInterface $entityManager,Access $access,Request $request): Response
    {
        if(!$access->hasRole('plugNoghreAdmin'))
            throw $this->createAccessDeniedException();
        $data = $entityManager->getRepository(Person::class)->findBy([
            'bid'=>$request->headers->get('activeBid'),
            'plugNoghreGhalam'=>true
        ]);
        return $this->json($data);
    }
    #[Route('/api/plugin/noghre/hakak/list', name: 'app_plug_noghre_employees_hakak_list')]
    public function app_plug_noghre_employees_hakak_list(EntityManagerInterface $entityManager,Access $access,Request $request): Response
    {
        if(!$access->hasRole('plugNoghreAdmin'))
            throw $this->createAccessDeniedException();
        $data = $entityManager->getRepository(Person::class)->findBy([
            'bid'=>$request->headers->get('activeBid'),
            'plugNoghreHakak'=>true
        ]);
        return $this->json($data);
    }
    #[Route('/api/plugin/noghre/morsa/list', name: 'app_plug_noghre_employees_morsa_list')]
    public function app_plug_noghre_employees_morsa_list(EntityManagerInterface $entityManager,Access $access,Request $request): Response
    {
        if(!$access->hasRole('plugNoghreAdmin'))
            throw $this->createAccessDeniedException();
        $data = $entityManager->getRepository(Person::class)->findBy([
            'bid'=>$request->headers->get('activeBid'),
            'plugNoghreMorsa'=>true
        ]);
        return $this->json($data);
    }
    #[Route('/api/plugin/noghre/tarash/list', name: 'app_plug_noghre_employees_tarash_list')]
    public function app_plug_noghre_employees_tarash_list(EntityManagerInterface $entityManager,Access $access,Request $request): Response
    {
        if(!$access->hasRole('plugNoghreAdmin'))
            throw $this->createAccessDeniedException();
        $data = $entityManager->getRepository(Person::class)->findBy([
            'bid'=>$request->headers->get('activeBid'),
            'plugNoghreTarash'=>true
        ]);
        return $this->json($data);
    }

    #[Route('/api/plugin/noghre/remove/order/{id}', name: 'app_plug_noghre_remove_order')]
    public function app_plug_noghre_remove_order(Log $log,Provider $provider, EntityManagerInterface $entityManager,Access $access,Request $request,String $id): Response
    {
        $acc = $access->hasRole('plugNoghreSell');
        if(!$acc)
            $acc = $access->hasRole('plugNoghreAdmin');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
            'code'=>$id,
            'bid'=>$acc['bid'],
            'money'=> $acc['money']
        ]);
        if(!$doc)
            throw $this->createNotFoundException();
        if($doc->getPlugin() != 'plugNoghreOrder')
            throw $this->createAccessDeniedException();
        $order=$entityManager->getRepository(PlugNoghreOrder::class)->findOneBy(['doc'=>$doc]);
        //delete pays
        $pays = $entityManager->getRepository(HesabdariRow::class)->findBy([
            'plugin'=>'plugNoghrePay',
            'refData'=>$order->getDoc()->getCode()
        ]);
        foreach($pays as $pay){
            $payDoc = $entityManager->getRepository(HesabdariDoc::class)->find($pay->getDoc()->getId());
            $resPays = $pays = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'doc'=>$payDoc,
            ]);
            foreach($resPays as $res){
                $entityManager->remove($res);
                $entityManager->flush();
            }
            $entityManager->remove($payDoc);
            $entityManager->flush();
        }
        $entityManager->remove($order);
        $rows=$entityManager->getRepository(HesabdariRow::class)->findBy(['doc'=>$doc]);
        foreach ($rows as $row){
            $entityManager->remove($row);
            $entityManager->flush();
        }
        $entityManager->flush();
        $entityManager->remove($doc);
        $entityManager->flush();
        $log->insert(
            'کارگاه منسوجات نقره',
        'سفارش مشتری با نام ' . $order->getCustomer()->getNikename() . ' حذف شد. ',
        $this->getUser(),
        $acc['bid']);
        return $this->json([
            'result'=>'ok'
        ]);

    }
    #[Route('/api/plugin/noghre/orders/list', name: 'app_plug_noghre_orders_list')]
    public function app_plug_noghre_orders_list(EntityManagerInterface $entityManager,Access $access,Request $request): Response
    {
        $acc = $access->hasRole('plugNoghreSell');
        if(!$acc)
            $acc = $access->hasRole('plugNoghreAdmin');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $items = $entityManager->getRepository(PlugNoghreOrder::class)->findBy([
            'bid'=>$request->headers->get('activeBid'),
        ]);
        $res = [];
        foreach ($items as $item){
            $temp = [];
            $temp['id']= $item->getId();
            $temp['customer']=$item->getCustomer()->getNikename();
            $temp['status'] = $item->getStatus();
            $temp['dateDeliver']= $item->getDeliveryDate();
            $temp['model']=$item->getRingModel();
            $temp['size']=$item->getRingSize();
            $temp['negin']=$item->getNegin();
            $temp['neginFee']=$item->getNeginFee();
            $temp['hakak']=$item->getHakak()->getNikename();
            $temp['code']=$item->getDoc()->getCode();
            $temp['price']=$item->getDoc()->getAmount();
            $res[] = $temp;
        }
        return $this->json($res);
    }

    #[Route('/api/plugin/noghre/submit/order/{id}', name: 'app_plug_noghre_submit_order')]
    public function app_plug_noghre_submit_order(Log $log,Provider $provider, EntityManagerInterface $entityManager,Access $access,Request $request,String $id): Response
    {
        $acc = $access->hasRole('plugNoghreSell');
        if(!$acc)
            $acc = $access->hasRole('plugNoghreAdmin');
        if(!$acc)
            throw $this->createAccessDeniedException();

        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(!array_key_exists('hakak',$params) ||
            !array_key_exists('ghalam',$params) ||
            !array_key_exists('tarash',$params) ||
            !array_key_exists('morsa',$params) ||
            !array_key_exists('morsaPrice',$params) ||
            !array_key_exists('tarashPrice',$params) ||
            !array_key_exists('ghalamPrice',$params) ||
            !array_key_exists('hakakPrice',$params) ||
            !array_key_exists('modelPrice',$params) ||
            !array_key_exists('etcPrice',$params) ||
            !array_key_exists('place',$params) ||
            !array_key_exists('model',$params) ||
            !array_key_exists('size',$params) ||
            !array_key_exists('noghreAmount',$params) ||
            !array_key_exists('noghreFee',$params) ||
            !array_key_exists('negin',$params) ||
            !array_key_exists('neginFee',$params) ||
            !array_key_exists('dateDeliver',$params) ||
            !array_key_exists('dateSubmit',$params) ||
            !array_key_exists('status',$params) ||
            !array_key_exists('des',$params)
        )
            throw $this->createAccessDeniedException('params incomplete');
        $customer = $entityManager->getRepository(Person::class)->findOneBy(['id'=>$params['customer']['id'], 'bid'=>$acc['bid']]);
        if(!$customer)
            throw $this->createAccessDeniedException('params not found');

        $hakak = $entityManager->getRepository(Person::class)->findOneBy(['id'=>$params['hakak']['id'], 'bid'=>$acc['bid']]);
        if(!$hakak)
            throw $this->createAccessDeniedException('params not found');

        $morsa = $entityManager->getRepository(Person::class)->findOneBy(['id'=>$params['morsa']['id'], 'bid'=>$acc['bid']]);
        if(!$morsa)
            throw $this->createAccessDeniedException('params not found');

        $tarash = $entityManager->getRepository(Person::class)->findOneBy(['id'=>$params['tarash']['id'], 'bid'=>$acc['bid']]);
        if(!$tarash)
            throw $this->createAccessDeniedException('params not found');

        $ghalam = $entityManager->getRepository(Person::class)->findOneBy(['id'=>$params['ghalam']['id'], 'bid'=>$acc['bid']]);
        if(!$ghalam)
            throw $this->createAccessDeniedException('params not found');
        $order = new PlugNoghreOrder();
        $order->setCustomer($entityManager->getRepository(Person::class)->find($params['customer']['id']));
        $order->setDeliveryDate($params['dateDeliver']);
        $order->setStatus($params['status']);
        $order->setPlace($params['place']);
        $order->setRingModel($params['model']);
        $order->setRingSize($params['size']);
        $order->setNoghreAmount($params['noghreAmount']);
        $order->setNoghreFee($params['noghreFee']);
        $order->setNegin($params['negin']);
        $order->setNeginFee($params['neginFee']);
        $order->setTarash($entityManager->getRepository(Person::class)->find($params['tarash']['id']));
        $order->setMorsa($entityManager->getRepository(Person::class)->find($params['morsa']['id']));
        $order->setHakak($entityManager->getRepository(Person::class)->find($params['hakak']['id']));
        $order->setGhalam($entityManager->getRepository(Person::class)->find($params['ghalam']['id']));
        $order->setBid($acc['bid']);
        $order->setDes($params['des']);

        //create hesabdari doc
        $doc = new HesabdariDoc();
        $doc->setDateSubmit(time());
        $doc->setSubmitter($this->getUser());
        $doc->setDes('سفارش ساخت منسوجات نقره: ' . $params['des']);
        $doc->setBid($acc['bid']);
        $doc->setPlugin('plugNoghreOrder');
        $doc->setMoney($acc['money']);
        $doc->setDate($params['dateSubmit']);
        $doc->setYear($acc['year']);
        $doc->setType('plug_noghre_order');
        $doc->setAmount($params['etcPrice'] + ($params['noghreAmount']*$params['noghreFee']) + $params['hakakPrice'] + $params['ghalamPrice'] + $params['tarashPrice'] + $params['morsaPrice']);
        $doc->setCode($provider->getAccountingCode($acc['bid'],'accounting'));
        $entityManager->persist($doc);
        $entityManager->flush();
        $order->setDoc($doc);

        //hesabdari rows
        //sell noghre
        $row = new HesabdariRow();
        $row->setPlugin('plugNoghreOrder');
        $row->setRefData('noghrePrice');
        $row->setDoc($doc);
        $row->setBid($acc['bid']);
        $row->setYear($acc['year']);
        $row->setBd(0);
        $row->setBs($params['noghreFee'] * $params['noghreAmount']);
        $row->setDes('استفاده از ' . $params['noghreAmount'] . '  گرم نقره با نرخ ' . $params['noghreFee'] . 'در سفارش مشتری');
        $row->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code'=>58]));
        $entityManager->persist($row);
        $entityManager->flush();
        
        //sell negin
        if($params['neginFee'] != 0){
            $row = new HesabdariRow();
            $row->setPlugin('plugNoghreOrder');
            $row->setRefData('neginPrice');
            $row->setDoc($doc);
            $row->setBid($acc['bid']);
            $row->setYear($acc['year']);
            $row->setBd(0);
            $row->setBs($params['neginFee']);
            $row->setDes('استفاده از نگین' . $params['negin'] . '  با نرخ ' . $params['neginFee'] . 'در سفارش مشتری');
            $row->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code'=>58]));
            $entityManager->persist($row);
            $entityManager->flush();
        }
        //sell tarash row
        if($params['tarashPrice'] != 0){
            $row = new HesabdariRow();
            $row->setPlugin('plugNoghreOrder');
            $row->setRefData('tarashPrice');
            $row->setDoc($doc);
            $row->setBid($acc['bid']);
            $row->setYear($acc['year']);
            $row->setBd(0);
            $row->setBs($params['tarashPrice']);
            $row->setDes('اجرت تراشکاری ' . $params['tarashPrice'] . '  تراشکار:  ' . $params['tarash']['nikename'] );
            $row->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code'=>58]));
            $entityManager->persist($row);
            $entityManager->flush();
        }
        //sell model price row
        if($params['modelPrice'] != 0){
            $row = new HesabdariRow();
            $row->setPlugin('plugNoghreOrder');
            $row->setRefData('modelPrice');
            $row->setDoc($doc);
            $row->setBid($acc['bid']);
            $row->setYear($acc['year']);
            $row->setBd(0);
            $row->setBs($params['modelPrice']);
            $row->setDes('اجرت اجرای مدل منسوجات نقره' );
            $row->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code'=>58]));
            $entityManager->persist($row);
            $entityManager->flush();
        }
        //sell hakak row
        if($params['hakakPrice'] != 0){
            $row = new HesabdariRow();
            $row->setPlugin('plugNoghreOrder');
            $row->setRefData('hakakPrice');
            $row->setDoc($doc);
            $row->setBid($acc['bid']);
            $row->setYear($acc['year']);
            $row->setBd(0);
            $row->setBs($params['hakakPrice']);
            $row->setDes('اجرت حکاکی ' . $params['hakakPrice'] . '  حکاک:  ' . $params['hakak']['nikename'] );
            $row->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code'=>58]));
            $entityManager->persist($row);
            $entityManager->flush();
        }
        //sell morsa row
        if($params['morsaPrice'] != 0){
            $row = new HesabdariRow();
            $row->setPlugin('plugNoghreOrder');
            $row->setRefData('morsaPrice');
            $row->setDoc($doc);
            $row->setBid($acc['bid']);
            $row->setYear($acc['year']);
            $row->setBd(0);
            $row->setBs($params['morsaPrice']);
            $row->setDes('اجرت مرصع کاری  ' . $params['morsaPrice'] . '  مرصع کار:  ' . $params['morsa']['nikename'] );
            $row->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code'=>58]));
            $entityManager->persist($row);
            $entityManager->flush();
        }
        //sell ghalam row
        if($params['ghalamPrice'] != 0){
            $row = new HesabdariRow();
            $row->setPlugin('plugNoghreOrder');
            $row->setRefData('ghalamPrice');
            $row->setDoc($doc);
            $row->setBid($acc['bid']);
            $row->setYear($acc['year']);
            $row->setBd(0);
            $row->setBs($params['ghalamPrice']);
            $row->setDes('اجرت قلم زنی  ' . $params['ghalamPrice'] . '  قلم زن:  ' . $params['ghalam']['nikename'] );
            $row->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code'=>58]));
            $entityManager->persist($row);
            $entityManager->flush();
        }

        //sell etc price row
        if($params['etcPrice'] != 0){
            $row = new HesabdariRow();
            $row->setPlugin('plugNoghreOrder');
            $row->setRefData('etcPrice');
            $row->setDoc($doc);
            $row->setBid($acc['bid']);
            $row->setYear($acc['year']);
            $row->setBd(0);
            $row->setBs($params['etcPrice']);
            $row->setDes('سایر هزینه‌ها' );
            $row->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code'=>58]));
            $entityManager->persist($row);
            $entityManager->flush();
        }

        //add person to bedehkari
        $row = new HesabdariRow();
        $row->setPlugin('plugNoghreOrder');
        $row->setRefData('customer');
        $row->setDoc($doc);
        $row->setBid($acc['bid']);
        $row->setYear($acc['year']);
        $row->setBd($params['etcPrice'] + ($params['noghreAmount']*$params['noghreFee']) + $params['hakakPrice'] + $params['ghalamPrice'] + $params['tarashPrice'] + $params['morsaPrice']);
        $row->setBs(0);
        $row->setDes('خرید کالا و خدمات (سفارش منسوجات نقره)' );
        $row->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code'=>8]));
        $row->setPerson($entityManager->getRepository(Person::class)->findOneBy(
            [
                'id'=>$params['customer']['id'],
                'bid'=>$acc['bid']
            ]
        ));
        $entityManager->persist($row);
        $entityManager->flush();

        $entityManager->persist($order);
        $entityManager->flush();

        $log->insert(
            'کارگاه منسوجات نقره',
        'سفارش مشتری ' . $order->getCustomer()->getNikename() . '  ویرایش / ایجاد شد.',
        $this->getUser(),
        $acc['bid']);

        return $this->json(['result'=>'ok']);
    }

    #[Route('/api/plugin/noghre/customer/info/{code}', name: 'app_plug_noghre_customer_info')]
    public function app_plug_noghre_customer_info($code,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('plugNoghreSell');
        if(!$acc)
            $acc = $access->hasRole('plugNoghreAdmin');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $person = $entityManager->getRepository(Person::class)->findOneBy([
            'bid'=>$acc['bid'],
            'code'=>$code
        ]);
        return $this->json($person);
    }

    #[Route('/api/plugin/noghre/order/info/{code}', name: 'app_plug_noghre_order_info')]
    public function app_plug_noghre_order_info($code,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('plugNoghreSell');
        if(!$acc)
            $acc = $access->hasRole('plugNoghreAdmin');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $items = $entityManager->getRepository(PlugNoghreOrder::class)->findOneBy([
            'bid'=>$acc['bid'],
            'id'=>$code
        ]);
        $info = $provider->Entity2Array($items,1,[]);
        $hakak = $entityManager->getRepository(HesabdariRow::class)->findOneBy([
            'doc'=>$items->getDoc(),
            'plugin'=>'plugNoghreOrder',
            'refData'=>'hakakPrice'
        ]);
        $info['hakakPrice'] = $provider->Entity2Array($hakak,1,[]);

        $tarashRow = $entityManager->getRepository(HesabdariRow::class)->findOneBy([
            'doc'=>$items->getDoc(),
            'plugin'=>'plugNoghreOrder',
            'refData'=>'tarashPrice'
        ]);
        $info['tarashRow'] = $provider->Entity2Array($tarashRow,1,[]);
        
        $ghalamRow = $entityManager->getRepository(HesabdariRow::class)->findOneBy([
            'doc'=>$items->getDoc(),
            'plugin'=>'plugNoghreOrder',
            'refData'=>'ghalamPrice'
        ]);
        $info['ghalamRow'] = $provider->Entity2Array($ghalamRow,1,[]);
        
        $etcPriceRow = $entityManager->getRepository(HesabdariRow::class)->findOneBy([
            'doc'=>$items->getDoc(),
            'plugin'=>'plugNoghreOrder',
            'refData'=>'etcPrice'
        ]);
        $info['etcPriceRow'] = $provider->Entity2Array($etcPriceRow,1,[]);

        $morsaRow = $entityManager->getRepository(HesabdariRow::class)->findOneBy([
            'doc'=>$items->getDoc(),
            'plugin'=>'plugNoghreOrder',
            'refData'=>'morsaPrice'
        ]);
        $info['morsaRow'] = $provider->Entity2Array($morsaRow,1,[]);

        $modelPriceRow = $entityManager->getRepository(HesabdariRow::class)->findOneBy([
            'doc'=>$items->getDoc(),
            'plugin'=>'plugNoghreOrder',
            'refData'=>'modelPrice'
        ]);
        $info['morsaRow'] = $provider->Entity2Array($modelPriceRow,1,[]);

        return $this->json(
            $info
        );
    }

    #[Route('/api/plugin/noghre/order/pays/list/{code}', name: 'app_plug_noghre_order_pays_list')]
    public function app_plug_noghre_order_pays_list($code,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('plugNoghreSell');
        if(!$acc)
            $acc = $access->hasRole('plugNoghreAdmin');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $order = $entityManager->getRepository(PlugNoghreOrder::class)->findOneBy([
            'bid'=>$acc['bid'],
            'id'=>$code
        ]);
    
        $rows = $entityManager->getRepository(HesabdariRow::class)->findBy([
            'plugin'=>'plugNoghrePay',
            'refData'=>$order->getDoc()->getCode()
        ]);
        $res = [];
        foreach ($rows as $item){
            $temp = [];
            $temp['id'] = $item->getid();
            $temp['code'] = $item->getDoc()->getCode();
            $temp['amount'] = $item->getBd();
            $temp['date'] = $item->getDoc()->getDate();
            $temp['des'] = $item->getDes();
            if($item->getBank()){
                $temp['type']='حساب بانکی';
                $temp['ref']=$item->getBank()->getName();
            }
            elseif($item->getCashdesk()){
                $temp['type']='صندوق';
                $temp['ref']=$item->getCashdesk()->getName();
            }
            elseif($item->getSalary()){
                $temp['type']='تنخواه گردان';
                $temp['ref']=$item->getSalary()->getName();
            }
            $res[] = $temp;
        }
        return $this->json($res);
    }

    #[Route('/api/plugin/noghre/order/pays/remove/{code}', name: 'app_plug_noghre_order_pays_remove')]
    public function app_plug_noghre_order_pays_remove($code,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {

        $acc = $access->hasRole('plugNoghreSell');
        if(!$acc)
            $acc = $access->hasRole('plugNoghreAdmin');
        if(!$acc)
            throw $this->createAccessDeniedException();
        
        $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
            'code'=>$code,
            'plugin'=>'plugNoghrePay',
            'bid'=>$acc['bid'],
            'money'=> $acc['money']
        ]);
        if(!$doc)
            throw $this->createNotFoundException();
        foreach($doc->getHesabdariRows() as $row){
            $entityManager->remove($row);
            $entityManager->flush();
        }
        
        $entityManager->remove($doc);
        $entityManager->flush();
        $log->insert(
            'کارگاه منسوجات نقره',
        'سند پرداخت سفارش حذف شد.',
        $this->getUser(),
        $acc['bid']);
        return $this->json(['result'=>1]);
    }

}
