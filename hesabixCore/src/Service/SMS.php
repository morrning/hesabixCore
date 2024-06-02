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
    private registryMGR $registryMGR;

    private int $smsPrice = 1500;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager,registryMGR $registryMGR)
    {
        $this->entityManager = $entityManager;
        $this->registryMGR = $registryMGR;
        $this->settings = $entityManager->getRepository(Settings::class)->findAll()[0];

    }

    public function send(array $params,$bodyID,$to): void
    {
        if($this->registryMGR->get('sms','plan') == 'melipayamak'){    
            try{
                        $username = $this->registryMGR->get('sms','username');
                        $password = $this->registryMGR->get('sms','password');
                        $api = new MelipayamakApi($username,$password);
                        $sms = $api->sms('soap');
                        $response = $sms->sendByBaseNumber($params,$to,$bodyID);
                        $json = json_decode($response);
                        
                    }catch(\Exception $e){
                        echo $e->getMessage();
                        die();
                    }

        }
        elseif($this->registryMGR->get('sms','plan') == 'idepayam'){
            ini_set("soap.wsdl_cache_enabled", "0");
            
            //create next
            $pt = [];
            foreach($params as $param){
                $pt['{' . strval(array_search($param,$params)) . '}'] = $param;
            }
            $soap = new \SoapClient("http://185.112.33.61/wbs/send.php?wsdl");
            $soap->token =  $this->registryMGR->get('sms','token');
            $soap->fromNum = $this->registryMGR->get('sms','fromNum');
            $soap->toNum = array($to);
            $soap->patternID = $bodyID;
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