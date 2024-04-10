<?php
namespace App\Service;
use App\Entity\Business;
use App\Entity\Registry;
use App\Entity\Settings;
use App\Entity\User;
use Melipayamak\MelipayamakApi;
use Doctrine\ORM\EntityManagerInterface;

class SMS
{
    private EntityManagerInterface $entityManager;
    private Settings $settings;

    private int $smsPrice = 2500;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->settings = $entityManager->getRepository(Settings::class)->findAll()[0];
    }

    public function send(array $params,$bodyID,$to): void
    {
        $settings = $this->entityManager->getRepository(Settings::class)->findAll()[0];
        if($settings->getActiveSmsPanel() == 'melipayamak'){
            try{
                        $username = $this->settings->getPayamakUsername();
                        $password = $this->settings->getPayamakPassword();
                        $api = new MelipayamakApi($username,$password);
                        $sms = $api->sms('soap');
                        $response = $sms->sendByBaseNumber($params,$to,$bodyID);
                        $json = json_decode($response);
                        echo $json->Value; //RecId or Error Number 
                    }catch(\Exception $e){
                        //echo $e->getMessage();
                    }

        }
        elseif($settings->getActiveSmsPanel() == 'idePayam'){
            ini_set("soap.wsdl_cache_enabled", "0");
            $patternID = $this->entityManager->getRepository(Registry::class)->findOneBy([
                'root'=>'sms',
                'name'=>$bodyID
            ]);
            $fromNum = $this->entityManager->getRepository(Registry::class)->findOneBy([
                'root'=>'sms',
                'name'=>'fromNum'
            ]);
            //create next
            $pt = [];
            foreach($params as $param){
                $pt['{' + array_search($param,$params) + '}'] = $param;
            }
            $soap = new \SoapClient("http://185.112.33.61/wbs/send.php?wsdl");
            $soap->token =  $this->settings->getMelipayamakToken();
            $soap->fromNum = $fromNum->getValueOfKey();
            $soap->toNum = array($to);
            $soap->patternID = $patternID->getValueOfKey();
            $soap->Content = json_encode($pt,JSON_UNESCAPED_UNICODE);
            $soap->Type = 0;
            $array = $soap->SendSMSByPattern($soap->fromNum, $soap->toNum, $soap->Content, $soap->patternID, $soap->Type, $soap->token);
            
        }

        
    }

    public function sendByBalance(array $params,$bodyID,$to,Business $business,User $user,$balance = 500): int
    {
       if($business->getSmsCharge() < ($balance * $this->smsPrice))
           return 2;
       $this->send($params,$bodyID,$to);
       $business->setSmsCharge($business->getSmsCharge() - ($balance * $this->smsPrice));
       $this->entityManager->persist($business);
       $this->entityManager->flush();
       //save logs
        $log = new \App\Entity\Log();
        $log->setBid($business);
        $log->setDateSubmit(time());
        $log->setPart('پیامک');
        $log->setUser($user);
        $log->setDes('ارسال پیامک به طول ' . $balance . ' پیامک به شماره  ' . $to . ' با شماره الگو ' . $bodyID . ' هزینه: ' . ($this->smsPrice * $balance) . ' ریال ');
        $this->entityManager->persist($log);
        $this->entityManager->flush();
       return 1;
    }

}