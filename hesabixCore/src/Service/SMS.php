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

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, registryMGR $registryMGR)
    {
        $this->entityManager = $entityManager;
        $this->registryMGR = $registryMGR;
        $this->settings = $entityManager->getRepository(Settings::class)->findAll()[0];
    }

    public function getSmsPrice(): int
    {
        $rootSystem = 'system_settings';
        return (int) $this->registryMGR->get($rootSystem, 'sms_price'); // گرفتن قیمت از رجیستری
    }

    public function send(array $params, $bodyID, $to): void
    {
        if ($this->registryMGR->get('sms', 'plan') == 'melipayamak') {
            try {
                $username = $this->registryMGR->get('sms', 'username');
                $password = $this->registryMGR->get('sms', 'password');
                $api = new MelipayamakApi($username, $password);
                $sms = $api->sms('soap');
                $response = $sms->sendByBalanceNumber($params, $to, $bodyID);
                $json = json_decode($response);
            } catch (\Exception $e) {
                echo $e->getMessage();
                die();
            }
        } elseif ($this->registryMGR->get('sms', 'plan') == 'idepayam') {
            ini_set("soap.wsdl_cache_enabled", "0");

            $pt = [];
            foreach ($params as $param) {
                $pt['{' . strval(array_search($param, $params)) . '}'] = $param;
            }
            $soap = new \SoapClient("http://185.112.33.61/wbs/send.php?wsdl");
            $soap->token = $this->registryMGR->get('sms', 'token');
            $soap->fromNum = $this->registryMGR->get('sms', 'fromNum');
            $soap->toNum = array($to);
            $soap->patternID = $bodyID;
            $soap->Content = json_encode($pt, JSON_UNESCAPED_UNICODE);
            $soap->Type = 0;
            $array = $soap->SendSMSByPattern($soap->fromNum, $soap->toNum, $soap->Content, $soap->patternID, $soap->Type, $soap->token);
        } elseif ($this->registryMGR->get('sms', 'plan') == 'ippanel') {
            $toArray = [$to];
            $username = $this->registryMGR->get('sms', 'username');
            $password = $this->registryMGR->get('sms', 'password');
            $from = $this->registryMGR->get('sms', 'fromNum');
            $input_data = [];
            foreach ($params as $key => $param) {
                $input_data['p' . strval(array_search($param, $params))] = $param;
            }
            $url = "https://ippanel.com/patterns/pattern?username=" . $username . "&password=" . urlencode($password) . "&from=$from&to=" . json_encode($toArray) . "&input_data=" . urlencode(json_encode($input_data)) . "&pattern_code=$bodyID";
            $handler = curl_init($url);
            curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($handler, CURLOPT_POSTFIELDS, $input_data);
            curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($handler);
        }
    }

    public function sendByBalance(array $params, $bodyID, $to, Business $business, User $user, $balance = 500): int
    {
        $smsPrice = $this->getSmsPrice(); // گرفتن قیمت دینامیک از رجیستری

        if ($business->getSmsCharge() < ($balance * $smsPrice))
            return 2;

        $this->send($params, $bodyID, $to);
        $business->setSmsCharge($business->getSmsCharge() - ($balance * $smsPrice));
        $this->entityManager->persist($business);
        $this->entityManager->flush();

        // ثبت لاگ
        $log = new \App\Entity\Log();
        $log->setBid($business);
        $log->setDateSubmit(time());
        $log->setPart('پیامک');
        $log->setUser($user);
        $log->setDes('ارسال پیامک به طول ' . $balance . ' پیامک به شماره  ' . $to . ' با شماره الگو ' . $bodyID . ' هزینه: ' . ($smsPrice * $balance) . ' ریال ');
        $this->entityManager->persist($log);
        $this->entityManager->flush();

        return 1;
    }
}