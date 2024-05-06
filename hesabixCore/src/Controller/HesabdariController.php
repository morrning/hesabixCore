<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Entity\Cashdesk;
use App\Entity\Cheque;
use App\Entity\Commodity;
use App\Entity\HesabdariDoc;
use App\Entity\HesabdariRow;
use App\Entity\HesabdariTable;
use App\Entity\Log as EntityLog;
use App\Entity\Money;
use App\Entity\PayInfoTemp;
use App\Entity\Person;
use App\Entity\PlugNoghreOrder;
use App\Entity\Salary;
use App\Entity\StoreroomTicket;
use App\Service\Access;
use App\Service\Jdate;
use App\Service\JsonResp;
use App\Service\Log;
use App\Service\Provider;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HesabdariController extends AbstractController
{
    private array $tableExport = [];
    #[Route('/api/accounting/doc/get', name: 'app_accounting_doc_get')]
    public function app_accounting_doc_get(Jdate $jdate,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(! array_key_exists('code',$params))
            $this->createNotFoundException();


        $acc = $access->hasRole('accounting');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
            'bid'=>$acc['bid'],
            'year'=>$acc['year'],
            'code'=>$params['code']
        ]);
        if(!$doc) throw $this->createNotFoundException();
        $rows = [];
        $rowsObj = $entityManager->getRepository(HesabdariRow::class)->findBy(
            ['doc'=>$doc]
        );
        foreach ($rowsObj as $item){
            $temp=[];
            $temp['id'] = $item->getId();
            $temp['bs'] = $item->getBs();
            $temp['bd'] = $item->getBd();
            $temp['des'] = $item->getDes();
            $temp['table'] = $item->getRef()->getName();
            $temp['referral'] = $item->getReferral();
            if($item->getPerson()){
                $temp['typeLabel'] = 'شخص';
                $temp['type'] = 'person';
                $temp['ref'] = $item->getPerson()->getNikeName();
                $temp['refCode'] = $item->getPerson()->getCode();
                $temp['person'] = [
                    'id' => $item->getPerson()->getId(),
                    'code' => $item->getPerson()->getCode(),
                    'nikename' => $item->getPerson()->getNikename(),
                    'name' => $item->getPerson()->getName(),
                    'tel' => $item->getPerson()->getTel(),
                    'mobile' => $item->getPerson()->getMobile(),
                    'address' => $item->getPerson()->getAddress(),
                    'des' => $item->getPerson()->getDes(),
                    'shomaresabt' => $item->getperson()->getSabt(),
                    'codeeghtesadi' =>$item->getPerson()->getCodeeghtesadi(),
                    'postalcode' => $item->getPerson()->getPostalCode()
                ];
            }
            elseif($item->getBank()){
                $temp['typeLabel'] = 'حسابهای بانکی';
                $temp['type'] = 'bank';
                $temp['ref'] = $item->getBank()->getName();
                $temp['refCode'] = $item->getBank()->getCode();
                $temp['bank'] = [
                    'id' => $item->getBank()->getId(),
                    'name' => $item->getBank()->getName(),
                    'cardNum' => $item->getBank()->getCardNum(),
                    'shaba' => $item->getBank()->getShaba(),
                    'accountNum' => $item->getBank()->getAccountNum(),
                    'owner' => $item->getBank()->getOwner(),
                    'shobe' => $item->getBank()->getShobe(),
                    'posNum' => $item->getBank()->getPosNum(),
                    'des' => $item->getBank()->getDes(),
                    'mobileInternetBank' => $item->getBank()->getMobileInternetBank(),
                    'code' => $item->getBank()->getCode(),
                ];
            }
            elseif($item->getCommodity()){
                $temp['typeLabel'] = 'موجودی کالا';
                $temp['type'] = 'commodity';
                $temp['ref'] = $item->getCommodity()->getName();
                $temp['refCode'] = $item->getCommodity()->getCode();
                $temp['count'] = $item->getCommdityCount();
                if($doc->getType() == 'sell')
                    $temp['unitPrice'] = $item->getBs()/$item->getCommdityCount();
                elseif($doc->getType() == 'buy')
                    $temp['unitPrice'] = $item->getBd()/$item->getCommdityCount();
                $temp['commodity'] = [
                    'id' => $item->getCommodity()->getId(),
                    'name' => $item->getCommodity()->getName(),
                    'des' => $item->getCommodity()->getDes(),
                    'code' => $item->getCommodity()->getCode(),
                    'unit' => $item->getCommodity()->getUnit()->getName(),
                ];
            }
            elseif($item->getSalary()){
                $temp['typeLabel'] = 'تنخواه گردان';
                $temp['type'] = 'salary';
                $temp['ref'] = $item->getSalary()->getName();
                $temp['refCode'] = $item->getSalary()->getCode();
                $temp['salary'] = [
                    'id' => $item->getSalary()->getId(),
                    'name' => $item->getSalary()->getName(),
                    'des' => $item->getSalary()->getDes(),
                    'code' => $item->getSalary()->getCode(),
                ];
            }
            elseif($item->getCashdesk()){
                $temp['typeLabel'] = 'صندوق';
                $temp['type'] = 'cashdesk';
                $temp['ref'] = $item->getCashdesk()->getName();
                $temp['refCode'] = $item->getCashdesk()->getCode();
                $temp['cashdesk'] = [
                    'id' => $item->getCashdesk()->getId(),
                    'name' => $item->getCashdesk()->getName(),
                    'des' => $item->getCashdesk()->getDes(),
                    'code' => $item->getCashdesk()->getCode(),
                ];
            }
            else{
                $temp['typeLabel'] = $item->getRef()->getName();
                $temp['type'] = 'calc';
                $temp['ref'] = $item->getRef()->getName();
                $temp['refCode'] = $item->getRef()->getCode();
            }
            $rows[] = $temp;
        }
        //get related docs
        $rds = [];
        foreach ($doc->getRelatedDocs() as $relatedDoc){
            $temp = [];
            $temp['amount'] = $relatedDoc->getAmount();
            $temp['des'] = $relatedDoc->getDes();
            $temp['date'] = $relatedDoc->getDate();
            $temp['type'] = $relatedDoc->getType();
            $temp['code'] = $relatedDoc->getCode();
            $rds[] = $temp;
        }
        return $this->json([
            'doc'=>JsonResp::SerializeHesabdariDoc($doc),
            'rows'=>$rows,
            'relatedDocs'=>$rds
        ]);
    }

    #[Route('/api/accounting/search', name: 'app_accounting_search')]
    public function app_accounting_search(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(! array_key_exists('type',$params))
            $this->createNotFoundException();
        $roll = '';
        if($params['type'] == 'person_receive' || $params['type'] == 'person_send') $roll='person';
        elseif($params['type'] == 'cost') $roll='cost';
        elseif($params['type'] == 'income') $roll='income';
        elseif($params['type'] == 'buy') $roll='buy';
        elseif($params['type'] == 'rfbuy') $roll='plugAccproRfbuy';
        elseif($params['type'] == 'transfer') $roll='transfer';
        elseif($params['type'] == 'sell') $roll='sell';
        elseif($params['type'] == 'rfsell') $roll='plugAccproRfsell';
        elseif($params['type'] == 'all') $roll='accounting';
        else
            $this->createNotFoundException();

        $acc = $access->hasRole($roll);
        if(!$acc)
            throw $this->createAccessDeniedException();
        if($params['type'] == 'all'){
            $data = $entityManager->getRepository(HesabdariDoc::class)->findBy([
                'bid'=>$acc['bid'],
                'year'=>$acc['year'],
            ],[
                'id'=>'DESC'
            ]);
        }
        else{
            $data = $entityManager->getRepository(HesabdariDoc::class)->findBy([
                'bid'=>$acc['bid'],
                'year'=>$acc['year'],
                'type'=>$params['type']
            ],[
                'id'=>'DESC'
            ]);
        }
        $dataTemp =[];
        foreach ($data as $item){
            $temp = [
                'id'=>$item->getId(),
                'dateSubmit'=>$item->getDateSubmit(),
                'date'=>$item->getDate(),
                'type'=>$item->getType(),
                'code'=>$item->getCode(),
                'des'=>$item->getDes(),
                'amount'=>$item->getAmount(),
                'submitter'=> $item->getSubmitter()->getFullName(),
            ];
            if($params['type'] == 'rfsell' || $params['type'] == 'rfbuy' || $params['type'] == 'buy' || $params['type'] == 'sell'){
                $mainRow = $entityManager->getRepository(HesabdariRow::class)->getNotEqual($item,'person');
                $temp['person'] = '';
                if($mainRow)
                    $temp['person'] = $mainRow->getPerson()->getNikename();
            }

            //get status of doc
            $temp['status'] = 'تسویه نشده';
            $pays = 0;
            foreach ($item->getRelatedDocs() as $relatedDoc){
                $pays += $relatedDoc->getAmount();

            }
            if($item->getAmount() <= $pays)
                $temp['status'] = 'تسویه شده';

            $dataTemp[] = $temp;
        }
        return $this->json($dataTemp);
    }

    /**
     * @throws \ReflectionException
     */
    #[Route('/api/accounting/insert', name: 'app_accounting_insert')]
    public function app_accounting_insert(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,Jdate $jdate): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(! array_key_exists('type',$params))
            $this->createNotFoundException();
        $roll = '';
        if($params['type'] == 'person_receive' || $params['type'] == 'person_send') $roll='person';
        else
            $roll = $params['type'];

        $acc = $access->hasRole($roll);
        if(!$acc)
            throw $this->createAccessDeniedException();

        if(!array_key_exists('rows',$params) || count($params['rows']) < 2)
            throw $this->createNotFoundException('rows is to short');
        if(!array_key_exists('date',$params) || !array_key_exists('des',$params))
            throw $this->createNotFoundException('some params mistake');
        if(array_key_exists('update',$params) && $params['update'] != ''){
            $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
                'bid'=>$acc['bid'],
                'year'=>$acc['year'],
                'code'=>$params['update']
            ]);
            if(!$doc) throw $this->createNotFoundException('document not found.');
            $doc->setDes($params['des']);
            $doc->setDate($params['date']);
            $doc->setMoney($acc['bid']->getMoney());
            if(array_key_exists('refData',$params))
                $doc->setRefData($params['refData']);
            if(array_key_exists('plugin',$params))
                $doc->setPlugin($params['plugin']);
           
            $entityManager->persist($doc);
            $entityManager->flush();
            $rows = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'doc'=>$doc
            ]);
            foreach ($rows as $row)
                $entityManager->remove($row);
        }
        else{
            $doc = new HesabdariDoc();
            $doc->setBid($acc['bid']);
            $doc->setYear($acc['year']);
            $doc->setDes($params['des']);
            $doc->setDateSubmit(time());
            $doc->setType($params['type']);
            $doc->setDate($params['date']);
            $doc->setSubmitter($this->getUser());
            $doc->setMoney($acc['bid']->getMoney());
            $doc->setCode($provider->getAccountingCode($acc['bid'],'accounting'));
            if(array_key_exists('refData',$params))
                $doc->setRefData($params['refData']);
            if(array_key_exists('plugin',$params))
                $doc->setPlugin($params['plugin']);
            $entityManager->persist($doc);
            $entityManager->flush();
        }

        //add document to related docs
        if(array_key_exists('related',$params)){
            $relatedDoc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy(['code'=>$params['related'],'bid'=>$doc->getBid()]);
            if($relatedDoc){
                $relatedDoc->addRelatedDoc($doc);
                $entityManager->persist($relatedDoc);
                $entityManager->flush();
            }
        }

        $amount = 0;
        foreach ($params['rows'] as $row){
            $row['bs'] = str_replace(',','',$row['bs']);
            $row['bd'] = str_replace(',','',$row['bd']);

            $hesabdariRow = new HesabdariRow();
            $hesabdariRow->setBid($acc['bid']);
            $hesabdariRow->setYear($acc['year']);
            $hesabdariRow->setDoc($doc);
            $hesabdariRow->setBs($row['bs']);
            $hesabdariRow->setBd($row['bd']);
            $ref = $entityManager->getRepository(HesabdariTable::class)->findOneBy([
                'code'=>$row['table']
            ]);
            $hesabdariRow->setRef($ref);

            $entityManager->persist($hesabdariRow);

            if(array_key_exists('referral',$row))
                $hesabdariRow->setReferral($row['referral']);
            $amount += $row['bs'];
            //check is type is person
            if($row['type'] == 'person'){
                $person = $entityManager->getRepository(Person::class)->find($row['id']);
                if(!$person) throw $this->createNotFoundException('person not found');
                elseif ($person->getBid()->getId() != $acc['bid']->getId()) throw $this->createAccessDeniedException('person is not in this business');
                $hesabdariRow->setPerson($person);
            }
            elseif($row['type'] == 'cheque'){
                $person = $entityManager->getRepository(Person::class)->findOneBy([
                    'bid'=> $acc['bid'],
                    'id'=>$row['chequeOwner']
                ]);
                $cheque = new Cheque();
                echo $hesabdariRow->getRef();
                $cheque->setBid($acc['bid']);
                $cheque->setSubmitter($this->getUser());
                $cheque->setPayDate($row['chequeDate']);
                $cheque->setBankOncheque($row['chequeBank']);
                $cheque->setRef($hesabdariRow->getRef());
                $cheque->setNumber($row['chequeNum']);
                $cheque->setSayadNum($row['chequeSayadNum']);
                $cheque->setDateSubmit(time());
                $cheque->setDes($row['des']);
                $dateArray = explode('-',$row['chequeDate']);
                $dateGre = strtotime($jdate->jalali_to_gregorian($dateArray['0'],$dateArray['1'],$dateArray['2'],'/'));
                $cheque->setDateStamp($dateGre);
                $cheque->setPerson($person);
                $cheque->setRef($entityManager->getRepository(HesabdariTable::class)->findOneBy(['code'=>$row['table']]));
                $cheque->setType($row['chequeType']);
                if($cheque->getType() == 'input')
                    $cheque->setAmount($hesabdariRow->getBd());
                else
                    $cheque->setAmount($hesabdariRow->getBs());
                $cheque->setLocked(false);
                $cheque->setRejected(false);
                $cheque->setStatus('پاس نشده');
                $entityManager->persist($cheque);
                $entityManager->flush();
                $hesabdariRow->setCheque($cheque);
            }
            elseif ($row['type'] == 'bank'){
                $bank = $entityManager->getRepository(BankAccount::class)->find($row['id']);
                if(!$bank) throw $this->createNotFoundException('bank not found');
                elseif ($bank->getBid()->getId() != $acc['bid']->getId()) throw $this->createAccessDeniedException('bank is not in this business');
                $hesabdariRow->setBank($bank);
            }
            elseif ($row['type'] == 'salary'){
                $salary = $entityManager->getRepository(Salary::class)->find($row['id']);
                if(!$salary) throw $this->createNotFoundException('salary not found');
                elseif ($salary->getBid()->getId() != $acc['bid']->getId()) throw $this->createAccessDeniedException('bank is not in this business');
                $hesabdariRow->setSalary($salary);
            }
            elseif ($row['type'] == 'cashdesk'){
                $cashdesk = $entityManager->getRepository(Cashdesk::class)->find($row['id']);
                if(!$cashdesk) throw $this->createNotFoundException('cashdesk not found');
                elseif ($cashdesk->getBid()->getId() != $acc['bid']->getId()) throw $this->createAccessDeniedException('bank is not in this business');
                $hesabdariRow->setCashdesk($cashdesk);
            }
            elseif ($row['type'] == 'commodity'){
                $row['count'] = str_replace(',','',$row['count']);
                $commodity = $entityManager->getRepository(Commodity::class)->find($row['commodity']['id']);
                if(!$commodity) throw $this->createNotFoundException('commodity not found');
                elseif ($commodity->getBid()->getId() != $acc['bid']->getId()) throw $this->createAccessDeniedException('$commodity is not in this business');
                $hesabdariRow->setCommodity($commodity);
                $hesabdariRow->setCommdityCount($row['count']);
            }
            
            if(array_key_exists('plugin',$row))
                $hesabdariRow->setPlugin($row['plugin']);
            if(array_key_exists('refData',$row))
                $hesabdariRow->setRefData($row['refData']);

            
            $hesabdariRow->setDes($row['des']);
            $entityManager->persist($hesabdariRow);
            $entityManager->flush();


        }
        $doc->setAmount($amount);
        $entityManager->persist($doc);
        $entityManager->flush();
        $log->insert(
            'حسابداری','سند حسابداری شماره ' . $doc->getCode() . ' ثبت / ویرایش شد.',
            $this->getUser(),
            $request->headers->get('activeBid'),
            $doc
        );

        return $this->json([
            'result'=>1,
            'doc'=>$provider->Entity2Array($doc,0)
        ]);
    }

    #[Route('/api/accounting/remove', name: 'app_accounting_remove_doc')]
    public function app_accounting_remove_doc(Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(! array_key_exists('code',$params))
            $this->createNotFoundException();
        $doc = $entityManager->getRepository(HesabdariDoc::class)->findOneBy([
            'code'=>$params['code'],
            'bid'=>$request->headers->get('activeBid')
        ]);
        if(!$doc) throw $this->createNotFoundException();
        $roll = '';
        if($doc->getType() == 'person_receive' || $doc->getType() == 'person_send')
            $roll = 'person';
        else
            $roll = $doc->getType();
        $acc = $access->hasRole($roll);
        if(!$acc)
            throw $this->createAccessDeniedException();
        $rows = $entityManager->getRepository(HesabdariRow::class)->findBy([
            'doc'=>$doc
        ]);
        if($doc->getPlugin() == 'plugNoghreOrder'){
            $order = $entityManager->getRepository(PlugNoghreOrder::class)->findOneBy([
                'doc'=>$doc
            ]);
            if($order)
                $entityManager->remove($order);
        }
        //check wallet online transactions
        $tempPays = $entityManager->getRepository(PayInfoTemp::class)->findOneBy(['doc'=>$doc]);
        if($tempPays){
            //doc has transaction
            return $this->json([
               'result'=>2,
                'message'=>'سند به دلیل داشتن تراکنش پرداخت آنلاین قابل حذف نیست.'
            ]);
        }
        //check storeroom tickets
        $tickets = $entityManager->getRepository(StoreroomTicket::class)->findBy(['doc'=>$doc]);
        foreach ($tickets as $ticket)
            $entityManager->remove($ticket);
        //remove rows and check sub systems
        foreach ($rows as $row){
            if($row->getCheque()){
                if($row->getCheque()->isLocked()){
                    //doc has transaction
                    return $this->json([
                        'result'=>2,
                        'message'=>'سند به دلیل داشتن تراکنش مرتبط با چک بانکی قابل حذف نیست.'
                    ]);
                }
                $log->insert('بانکداری','چک  شماره  شماره ' . $row->getCheque()->getNumber() . ' حذف شد.',$this->getUser(),$request->headers->get('activeBid'));
                $entityManager->remove($row->getCheque());
            }
            $entityManager->remove($row);
        }
            
        foreach ($doc->getRelatedDocs() as $relatedDoc){
            if($relatedDoc->getType() != 'walletPay'){
                $items = $entityManager->getRepository(HesabdariRow::class)->findBy(['doc'=>$relatedDoc]);
                foreach ($items as $item)
                    $entityManager->remove($item);
                $entityManager->remove($relatedDoc);
                $log->insert('حسابداری','سند حسابداری شماره ' . $relatedDoc->getCode() . ' حذف شد.',$this->getUser(),$request->headers->get('activeBid'));
            }
        }

        //delete logs from documents
        $logs = $entityManager->getRepository(EntityLog::class)->findBy(['doc'=>$doc]);
        foreach($logs as $item){
            $item->setDoc(null);
            $entityManager->persist($item);
            $entityManager->flush();
        }
        $entityManager->remove($doc);
        $entityManager->flush();
        $log->insert('حسابداری','سند حسابداری شماره ' . $doc->getCode() . ' حذف شد.',$this->getUser(),$request->headers->get('activeBid'));
        return $this->json(['result'=>1]);
    }

    #[Route('/api/accounting/rows/search', name: 'app_accounting_rows_search')]
    public function app_accounting_rows_search(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(! array_key_exists('type',$params))
            $this->createNotFoundException();
        $roll = '';
        if($params['type'] == 'person') $roll='person';
        elseif($params['type'] == 'all') $roll='accounting';
        else
            $this->createNotFoundException();

        $acc = $access->hasRole($roll);
        if(!$acc)
            throw $this->createAccessDeniedException();
        if($params['type'] == 'person'){
            $person = $entityManager->getRepository(Person::class)->findOneBy([
                'bid'=>$acc['bid'],
                'code'=>$params['id'],
            ]);
            if(!$person)
                throw $this->createNotFoundException();

            $data = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'person'=> $person,
            ],[
                'id'=>'DESC'
            ]);
        }
        elseif($params['type'] == 'bank'){
            $bank = $entityManager->getRepository(BankAccount::class)->findOneBy([
                'bid'=>$acc['bid'],
                'code'=>$params['id'],
            ]);
            if(!$bank)
                throw $this->createNotFoundException();

            $data = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'bank'=> $bank,
            ],[
                'id'=>'DESC'
            ]);
        }
        elseif($params['type'] == 'cashdesk'){
            $cashdesk = $entityManager->getRepository(Cashdesk::class)->findOneBy([
                'bid'=>$acc['bid'],
                'code'=>$params['id'],
            ]);
            if(!$cashdesk)
                throw $this->createNotFoundException();

            $data = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'cashdesk'=> $cashdesk,
            ],[
                'id'=>'DESC'
            ]);
        }
        elseif($params['type'] == 'salary'){
            $salary = $entityManager->getRepository(Salary::class)->findOneBy([
                'bid'=>$acc['bid'],
                'code'=>$params['id'],
            ]);
            if(!$salary)
                throw $this->createNotFoundException();

            $data = $entityManager->getRepository(HesabdariRow::class)->findBy([
                'salary'=> $salary,
            ],[
                'id'=>'DESC'
            ]);
        }
        $dataTemp =[];
        foreach ($data as $item){
            $temp = [
                'id'=>$item->getId(),
                'dateSubmit'=>$item->getDoc()->getDateSubmit(),
                'date'=>$item->getDoc()->getDate(),
                'type'=>$item->getDoc()->getType(),
                'ref'=>$item->getRef()->getName(),
                'des'=>$item->getDes(),
                'bs'=>$item->getBs(),
                'bd'=>$item->getBd(),
                'code'=>$item->getDoc()->getCode(),
                'submitter'=> $item->getDoc()->getSubmitter()->getFullName()
            ];
            $dataTemp[] = $temp;
        }
        return $this->json($dataTemp);
    }

    #[Route('/api/accounting/table/get', name: 'app_accounting_table_get')]
    public function app_accounting_table_get(Jdate $jdate,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {

        $acc = $access->hasRole('accounting');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $temp =[];
        $nodes = $entityManager->getRepository(HesabdariTable::class)->findAll();
        foreach ($nodes as $node){
            if($this->hasChild($entityManager,$node)){
                $temp[$node->getCode()]=[
                    'text'=>$node->getName(),
                    'children'=>$this->getChildsLabel($entityManager,$node)
                ];
            }
            else{
                $temp[$node->getCode()]=[
                    'text'=>$node->getName(),
                ];
            }
        }
        return $this->json($temp);
    }

    #[Route('/api/accounting/table/childs/{type}', name: 'app_accounting_table_childs')]
    public function app_accounting_table_childs(string $type,Jdate $jdate,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole($type);
        if(!$acc)
            throw $this->createAccessDeniedException();

        if($type == 'cost'){
            $cost= $entityManager->getRepository(HesabdariTable::class)->findOneBy(['code'=>67]);
            return $this->json($this->getChilds($entityManager,$cost));
        }
        elseif($type == 'income'){
            $income= $entityManager->getRepository(HesabdariTable::class)->findOneBy(['code'=>56]);
            return $this->json($this->getChilds($entityManager,$income));
        }

        return $this->json([]);
    }

    private function getChildsLabel(EntityManagerInterface $entityManager, mixed $node){
        $childs =  $entityManager->getRepository(HesabdariTable::class)->findBy([
            'upper'=>$node
        ]);
        $temp = [];
        foreach ($childs as $child){
            $temp[] = $child->getCode();
        }
        return $temp;
    }

    private function hasChild(EntityManagerInterface $entityManager, mixed $node)
    {
        if(count($entityManager->getRepository(HesabdariTable::class)->findBy([
            'upper'=>$node
        ]))!= 0)
            return true;
        return false;
    }

    private function getChilds(EntityManagerInterface $entityManager, mixed $node){
        $childs =  $entityManager->getRepository(HesabdariTable::class)->findBy([
            'upper'=>$node
        ]);
        $temp = [];
        foreach ($childs as $child){
            if ($child->getType() == 'calc'){
                if($this->hasChild($entityManager,$child)){
                    $temp[]=[
                        'id'=>$child->getCode(),
                        'label'=>$child->getName(),
                        'children'=>$this->getChilds($entityManager,$child)
                    ];
                }
                else{
                    $temp[]=[
                        'id'=>$child->getCode(),
                        'label'=>$child->getName(),
                    ];
                }
            }
        }
        return $temp;
    }

    
}
